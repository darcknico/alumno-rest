# La caja

---

- [Diarias](#diarias)
- [Resumenes](#resumenes)

<a name="diarias"></a>
## Diarias

Es usado para llevar un registro de los movimientos de ingreso o egreso realizados en el dia. Permite ver los saldos del dia y movimientos de la caja. En ellas esta caracterizado por lo siguiente:

- Una diaria lleva dos sumadore o saldos. Por un lado los movimientos que son en EFECTIVO y por otro lado las demas formas de pago.

- Una diaria obtiene su saldo anterior a la diaria anterior mas cercana con respecto a la fecha.

- El cierre de una diaria implica actualizar los saldos del dia.

- La creacion de una diaria solo es posible si la anterior esta cerrada.

- Crear una diaria nueva, entre diarias, es posible si se indica una fecha en donde no exista otra diaria.

- Eliminar una diaria actualiza los saldos de las diarias concecuentas.

- Con respecto a los movimientos. Registrar un movimiento implica que la diaria a la que pertenece y las consecuentes a ella seran actualizadas.

- Con respecto a los movimientos. Editar un movimiento implica que la diaria a la que pertenece y las consecuentes a ella seran actualizadas.

- Con respecto a los movimientos. Eliminar un movimiento implica que la diaria a la que pertenece y las consecuentes a ella seran actualizadas.

- Las diarias se contabilizan sus saldos hasta dentro del mes. Si una diaria ocupa dias de dos meses, el mes que pertenece es de acuerdo al dia que fue creado.

<a name="resumenes"></a>
## Resumenes