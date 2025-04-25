# Sistema de Gestión de Nóminas - El Agreval

Este módulo proporciona funcionalidades completas para la gestión de nóminas, incluyendo:

1. Administración de salarios base para los empleados
2. Generación de nóminas considerando días trabajados y horas extra
3. Cálculo automático de deducciones (ISR, IMSS, AFORE, INFONAVIT)
4. Generación de PDFs con recibos de nómina
5. Historial completo de pagos para RRHH y empleados

## Instalación

1. Ejecuta el script SQL de actualización en `nomina/sql_update.sql` para actualizar la estructura de la base de datos.
2. Instala las dependencias de PHP necesarias mediante Composer:

```bash
composer require mpdf/mpdf
```

3. Asegúrate de que el directorio `uploads/nominas/pdf` exista y tenga permisos de escritura:

```bash
mkdir -p uploads/nominas/pdf
chmod 777 uploads/nominas/pdf
```

## Uso para Recursos Humanos

1. Acceda al panel de RRHH y haga clic en "Gestionar Nómina"
2. Primero, configure los salarios base para todos los empleados en "Gestionar Salarios Base"
3. Para generar una nueva nómina, haga clic en "Generar Nueva Nómina"
4. Complete los datos necesarios:
   - Seleccione el empleado
   - Indique los días trabajados
   - Si corresponde, agregue las horas extra trabajadas y la tarifa
   - Especifique el periodo de pago
   - Agregue otras deducciones si es necesario
5. El sistema calculará automáticamente:
   - Salario bruto
   - ISR (Impuesto Sobre la Renta)
   - IMSS (Seguro Social)
   - AFORE (Pensión)
   - INFONAVIT
   - Salario neto
6. Se generará un PDF que podrá compartir con el empleado
7. Todas las nóminas quedan registradas en el historial

## Uso para Empleados

1. Los empleados pueden acceder a sus recibos de nómina desde su dashboard
2. Pueden ver el historial completo de sus pagos
3. Pueden descargar los PDFs de sus recibos de nómina

## Cálculos

El sistema realiza los siguientes cálculos:

1. **Salario por días trabajados** = (Salario Base / 30) * Días Trabajados
2. **Horas Extra** = Número de Horas * Tarifa por Hora
3. **Salario Bruto** = Salario por Días Trabajados + Horas Extra
4. **Deducciones**:
   - ISR: Basado en tablas oficiales del SAT (simplificado para este ejemplo)
   - IMSS: 3.4% del salario bruto
   - AFORE: 1.65% del salario bruto
   - INFONAVIT: 5% del salario bruto
   - Otras deducciones: Especificadas manualmente
5. **Salario Neto** = Salario Bruto - Todas las Deducciones

## Notas

- Los porcentajes usados para las deducciones son aproximados y pueden necesitar ajustes.
- Para un entorno de producción, se recomienda verificar las tablas actualizadas del SAT.

## Soporte

Para cualquier problema, contacte al administrador del sistema. 