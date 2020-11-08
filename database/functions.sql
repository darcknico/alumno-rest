DELIMITER $$
CREATE OR REPLACE FUNCTION `cobranza_mes`(
	`id_plan_pago` INTEGER,
	`mes` INTEGER
)
RETURNS decimal(10,2)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	DECLARE id_tipo_obligacion INT;
	DECLARE total DECIMAL(10,2);
	IF mes = 2 THEN
		SET id_tipo_obligacion = 10;
	ELSE
		SET id_tipo_obligacion = 1;
	END IF;

	SELECT sum(opa.opa_monto) as monto
	INTO total
	FROM tbl_obligaciones obl
		RIGHT JOIN tbl_obligacion_pago opa ON obl.obl_id = opa.obl_id
		RIGHT JOIN tbl_pagos pag on opa.pag_id = pag.pag_id
	WHERE
		opa.estado = 1 and
		obl.ppa_id = id_plan_pago and
		obl.estado = 1 and
		obl.tob_id = id_tipo_obligacion and
		pag.estado = 1 and
		pag.tpa_id in (1,10) and
		month(obl.obl_fecha) = mes
	GROUP BY obl.obl_id;
	RETURN total;
END
$$
DELIMITER ;


