#include "BasicGenerator.hpp"
#include "BlurTileGenerator.hpp"
#include "FractalValueNoiseTileGenerator.hpp"
#include "Generator.hpp"

namespace hlt {
namespace mapgen {

/**
 * Generate a map based on parameters.
 * @param[out] map The map to generate.
 * @param parameters The parameters to use.
 */
void Generator::generate(Map &map, const MapParameters &parameters) {
    switch (parameters.type) {
    case MapType::Basic:
        BasicGenerator(parameters).generate(map);
        break;
    case MapType::BlurTile:
        BlurTileGenerator(parameters).generate(map);
        break;
    case MapType::Fractal:
        FractalValueNoiseTileGenerator(parameters).generate(map);
        break;
    }
}

/**
 * Read a MapType from command line input.
 * @param istream The input stream.
 * @param[out] type The MapType to read.
 * @return The input stream.
 */
std::istream &operator>>(std::istream &istream, MapType &type) {
    std::string type_string;
    istream >> type_string;
    if (type_string == "basic") {
        type = MapType::Basic;
    } else if (type_string == "blur_tile") {
        type = MapType::BlurTile;
    } else if (type_string == "fractal") {
        type = MapType::Fractal;
    } else {
        throw std::runtime_error("Illegal map type");
    }
    return istream;
}

}
}
