package com.arekalov.jsfgraph

import lombok.Data
import lombok.NoArgsConstructor
import java.io.Serializable

@Data
@NoArgsConstructor
class CoordinateHandlerBean : Serializable {
    var x = 0.0
    var y = 0.0
    var r = 0.0
}