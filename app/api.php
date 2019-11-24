<?php
/**
 * @license Apache 2.0
 */
/**
 * @OA\Info(
 *     description="Documentación para la API REST del sistema de Gestion de Alumnos",
 *     version="1.0.0",
 *     title="Alumno",
 *     @OA\Contact(
 *         email="neraricardo2013@gmail.io"
 *     ),
 * )
 */
/**
 * @OA\Tag(
 *     name="Alumnos",
 *     description="Todo lo que tenga que ver con Alumnos",
 * )
 * @OA\Tag(
 *     name="Sedes",
 *     description="Operaciones realizables en las Sedes",
 * )
 * @OA\Tag(
 *     name="Departamentos",
 *     description="Departamentos asignables a las carreras",
 * )
 * @OA\Tag(
 *     name="Modalidades",
 *     description="Modalidad de cursada e inscripcion de la Carrera/Materia",
 * )
 * @OA\Tag(
 *     name="Becas",
 *     description="Becas disponibles para la inscripcion y planes de pagos",
 * )
 * @OA\Tag(
 *     name="Carreras",
 *     description="Gestion de las Carreras",
 * )
 * @OA\Tag(
 *     name="PlanesEstudios",
 *     description="Planes de estudios para las carreras",
 * )
 * @OA\Tag(
 *     name="Materias",
 *     description="Materias del Plan de estudio",
 * )
 *
 * @OA\Parameter(
 *   name="id_sede",
 *   in="path",
 *   description="Identificación de la sede",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_alumno",
 *   in="path",
 *   description="Identificación del alumno",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_departamento",
 *   in="path",
 *   description="Identificación del departamento",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_modalidad",
 *   in="path",
 *   description="Identificación de la modalidad",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_beca",
 *   in="path",
 *   description="Identificación de la beca",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_carrera",
 *   in="path",
 *   description="Identificación de la carrera",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_plan_estudio",
 *   in="path",
 *   description="Identificación del plan de estudio",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 * @OA\Parameter(
 *   name="id_materia",
 *   in="path",
 *   description="Identificación de la materia",
 *   required=true,
 *   @OA\Schema(
 *     type="integer"
 *   )
 * ),
 */