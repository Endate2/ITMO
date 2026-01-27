package com.arekalov.jsfgraph.entity

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Entity
@Table(name = "point_model")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
data class ResultEntity(
    @Id
    @GeneratedValue(strategy = GenerationType.SEQUENCE, generator = "sequence-generator")
    @SequenceGenerator(name = "sequence-generator", sequenceName = "point_model_id_seq", allocationSize = 1)
    var id: Long = 0,
    var x: Double = 0.0,
    var y: Double = 0.0,
    var r: Double = 0.0,
    var result: Boolean = false
)
