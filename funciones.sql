DELIMITER //
DROP FUNCTION IF EXISTS asistencia_promedio //
CREATE FUNCTION asistencia_promedio(id_comision INTEGER,id_alumno INTEGER)
	RETURNS DECIMAL(10,2)
	BEGIN
		DECLARE estado INTEGER;
		DECLARE prom DECIMAL(10,2);
		select asa.estado, AVG( IF (asa.taa_id = 4,1,0 ) ) as avg
		INTO estado, prom
		from tbl_asistencia_alumno asa
		right join tbl_asistencias asi on asa.asi_id = asi.asi_id
		where
		asa.alu_id = id_alumno and
		asi.com_id = id_comision AND
		asa.estado = 1
		group by asa.estado;
		RETURN prom;
	END //

DELIMITER ;
