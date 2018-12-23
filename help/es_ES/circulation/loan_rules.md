#### Reglas de préstamo

Esta es una facilidad para definir reglas de préstamo basadas en:
- Tipo de miembro,
- Tipo de colección, y
- GMD.

Las reglas establecidas en esta funcionalidad son:
- Limitar el número de ejemplares por préstamo (Límite del préstamos),
- Período de tiempo del préstamo (Período del préstamo),
- Limitar la extensión del préstamo (Límite para volver a prestar),
- Pena por día vencido (Multa para cada día), y
- Tolerancia para los morosos (Período de gracia para el atraso).

Un ejemplo de definición de reglas de préstamo sería:

1. En la biblioteca tiene 3 tipos de colecciones: libros, audiovisuales (AV) y tesis.
2. Uno de los tipos de membresía es: préstamos estudiantiles, con una asignación total de 2 ejemplares, a saber: un ejemplar de la colección de libros y otro más de la colección de AV.
3. Para ello, tendrá que crear el tipo de membresía: "Estudiantes", con un préstamo total de dos colecciones.
4. Luego, las Reglas de préstamo se deben definir como:
- Tipo de miembro "Estudiante", la asignación de préstamos para colección = "Libro" es 1.
- Tipo de miembro "Estudiante", la asignación de préstamos para la colección = "AV" es 1.
- Tipo de miembro "Estudiante", la asignación de préstamos para la colección = "Tesis" es 0.

Todo debe ser definido, de lo contrario podrían superarse los límites deseados.
