<?php

class Game
{
    /**
     * @var string
     */
    private $botName;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Map
     */
    private $map;

    /**
     * @var Player[]
     */
    private $players;

    /**
     * @var Ship[]
     */
    private $ships;

    /**
     * @var Planet[]
     */
    private $planets;

    public function __construct(string $botName, Logger $logger, Connection $connection)
    {
        $logger->log(PHP_VERSION);
        $this->botName = $botName;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->initialize();
    }

    private function initialize()
    {
        $this->logger->log('Initialize new Game for Bot '.$this->botName);
        $playerId = (int) $this->connection->read();
        $this->logger->log('Player Id '.$playerId.' received');

        $mapSize = $this->connection->read();
        list($width, $height) = explode(' ', $mapSize);
        $this->logger->log(sprintf('Map Size %dx%d initialized', $width, $height));
        $this->map = new Map($playerId, (int) $width, (int) $height);

        $this->connection->send($this->botName);
    }

    public function updateMap(): Map
    {
        $this->players = [];
        $this->ships = [];
        $this->planets = [];

        $tokenizer = new Tokenizer($this->connection->read());
        $numPlayers = $tokenizer->nextInt();

        $this->logger->log('Number of Players: '.$numPlayers);
        for ($i = 0; $i < $numPlayers; $i++) {
            $this->parsePlayer($tokenizer);
        }

        $numPlanets = $tokenizer->nextInt();
        $this->logger->log('Number of Planets: '.$numPlanets);
        for ($i = 0; $i < $numPlanets; $i++) {
            $this->parsePlanet($tokenizer);
        }

        foreach ($this->ships as $ship) {
            $ship->setPlanet($this->planets[$ship->getPlanetId()] ?? null);
        }

        $this->map->update($this->players, $this->ships, $this->planets);

        return $this->map;
    }

    private function parsePlayer(Tokenizer $tokenizer): Player
    {
        $playerId = $tokenizer->nextInt();
        $player = new Player($playerId);

        $numShips = $tokenizer->nextInt();

        $this->logger->log('Parse Player '.$playerId.' with '.$numShips.' Ships');
        $ships = [];
        for ($i = 0; $i < $numShips; $i++) {
            $ships[] = $this->parseShip($player, $tokenizer);
        }

        $player->setShips($ships);
        $this->logger->log('Player: '.json_encode($player));
        $this->players[$playerId] = $player;

        return $player;
    }

    private function parseShip(Player $player, Tokenizer $tokenizer): Ship
    {
        $shipId = $tokenizer->nextInt();

        $coordinate = new Coordinate($tokenizer->nextFloat(), $tokenizer->nextFloat());
        $health = $tokenizer->nextFloat();
        $velocity = new Velocity($tokenizer->nextFloat(), $tokenizer->nextFloat());
        $docked = $tokenizer->nextBool();
        $planetId = $tokenizer->nextInt();
        $dockingProgress = $tokenizer->nextFloat();
        $weaponCooldown = $tokenizer->nextFloat();

        $ship = new Ship($player, $shipId, $coordinate, $health, $velocity, $docked, $planetId, $dockingProgress, $weaponCooldown);
        $this->logger->log('Ship: '.json_encode($ship));
        $this->ships[$shipId] = $ship;

        return $ship;
    }

    private function parsePlanet(Tokenizer $tokenizer): Planet
    {
        $planetId = $tokenizer->nextInt();
        $coordinate = new Coordinate($tokenizer->nextFloat(), $tokenizer->nextFloat());
        $health = $tokenizer->nextInt();
        $radius = $tokenizer->nextFloat();
        $dockingSpots = $tokenizer->nextInt();
        $production = $tokenizer->nextInt();
        $remainingProduction = $tokenizer->nextInt();
        $isOwned = $tokenizer->nextBool();
        $ownerId = $tokenizer->nextInt();
        $dockedShips = $tokenizer->nextInt();

        $ships = [];
        for ($i = 0; $i < $dockedShips; $i++) {
            $shipId = $tokenizer->nextInt();
            $ships[$shipId] = $this->ships[$shipId];
        }

        $planet = new Planet(
            $this->players[$ownerId] ?? null,
            $planetId,
            $coordinate,
            $health,
            $radius,
            $dockingSpots,
            $production,
            $remainingProduction,
            $isOwned,
            $dockedShips,
            $ships
        );
        $this->logger->log('Planet: '.json_encode($planet));
        $this->planets[$planetId] = $planet;

        return $planet;
    }
}
