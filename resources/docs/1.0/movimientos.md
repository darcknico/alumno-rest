# Movimientos

---

- [Tipos de movimientos](#tipos_movimiento)
- [Movimientos](#movimientos)

<a name="tipos_movimiento"></a>
## Tipos de movimientos

Los nuevos ingresos/egresos se le deben asociar a que tipo de movimiento pertenecen. Para categorizarlos y realizar estadisticas.
Por defecto los tipos de movimientos exsistentes son:

| # | Nombre   | Ingreso | Egreso |
| : |   :-   |  :  | : |
| 1 | Pago a cuotas | X  | - |
| 2 | Pago de mtaricula | X  | - |
| 3 | Otros | X | X |

El formulario es el siguiente:

- Nombre identificatorio: campo obligatorio de hasta 191 caracteres
- Descripción del movimiento
- Tipo de movimiento: Egreso - Ingreso

![image](/imagenes/documentacion/formulario_tipo_movimiento.png)

<a name="movimientos"></a>
## Movimientos

Los movimientos son del tipo ingreso o egreso. Cada uno para su registro presenta su unico formulario.

Formulario para INGRESOS:
- Monto: obligatorio y mayor que 0 (cero)
- Fecha de registro
- Forma de pago: Efectivo - Cheque - Tarjeta de credito o debito - Otro - Mercado Pago - Transferencia bancaria
- Tipo de movimiento
- Descripción
![image](/imagenes/documentacion/formulario_movimiento_ingreso.png)

Formulario para EGRESOS:
- Monto: obligatorio y mayor que 0 (cero)
- Fecha de registro
- Tipo de comprobante: FACTURA A/B/C y RECIBO X
- Numero de factura
- Forma de pago: Efectivo - Cheque - Tarjeta de credito o debito - Otro - Mercado Pago - Transferencia bancaria
- Tipo de movimiento
- Descripción
![image](/imagenes/documentacion/formulario_movimiento_egreso.png)

Formulario editable:
![image](/imagenes/documentacion/formulario_movimiento_editar.png)