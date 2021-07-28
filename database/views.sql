
select
tbl_alumnos.alu_id as alumno_id,
tbl_alumnos.alu_documento as  alumno_documento,
tbl_comisiones.mat_id as materia_id,
tbl_comisiones.com_aula_virtual_id as virtual_id,
tbl_comision_alumno.ins_id as inscripcion_id,
NULL as inscripcion_fecha,
"Curso" as tipo
from tbl_comision_alumno
inner join tbl_alumnos on tbl_comision_alumno.alu_id=tbl_alumnos.alu_id
inner join tbl_comisiones on tbl_comision_alumno.com_id=tbl_comisiones.com_id
UNION ALL
select
tbl_alumnos.alu_id as alumno_id,
tbl_alumnos.alu_documento as alumno_documento,
tbl_mesa_materia.mat_id as materia_id,
tbl_mesa_materia.mma_examen_virtual_id as virtual_id,
tbl_mesa_alumno_materia.mam_id as inscripcion_id,
tbl_mesa_materia.mma_fecha as inscripcion_fecha,
"Examen" as tipo
from tbl_mesa_alumno_materia
inner join tbl_alumnos on tbl_mesa_alumno_materia.alu_id=tbl_alumnos.alu_id
inner join tbl_mesa_materia on tbl_mesa_alumno_materia.mma_id=tbl_mesa_materia.mma_id

