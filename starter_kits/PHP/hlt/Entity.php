<?php

namespace hlt;

class Entity {
    /**
     * @var PlayerId
     */
    public $owner;

    /**
     * @var EntityId
     */
    public $id;

    /**
     * @var Position
     */
    public $position;

    public function __construct( PlayerId $owner,  EntityId $id,  Position $position) {
        $this->owner = $owner;
        $this->id = $id;
        $this->position = $position;
    }

//    @Override
//    public boolean equals(Object o) {
//        if (this == o) return true;
//        if (o == null || getClass() != o.getClass()) return false;
//
//        Entity entity = (Entity) o;
//
//        if (!owner.equals(entity.owner)) return false;
//        if (!id.equals(entity.id)) return false;
//        return position.equals(entity.position);
//    }
//
//    @Override
//    public int hashCode() {
//        int result = owner.hashCode();
//        result = 31 * result + id.hashCode();
//        result = 31 * result + position.hashCode();
//        return result;
//    }
}
