================================================================================
            TECNICAS CONTABLES - Sistema de Contabilidad Integral & Kardex
================================================================================

INTEGRANTES DEL EQUIPO
----------------------
  - DIEGO ALESSANDRO ROMERO MONTOYA
  - JUAN PABLO HENRIQUEZ MONTANO


EJECUCIÓN EN WAMP SERVER (RECOMENDADO)
--------------------------------------
  1. Con WAMP Server iniciado (ícono verde en la bandeja del sistema).
  2. Abra su navegador web e ingrese a:
         http://localhost/TecnicasContablesSoftware/

  (Opcional para máquinas sin WAMP: hacer doble clic en ejecutar.bat, el cual
  levanta el PHP portable en http://localhost:8000/).


ESTRUCTURA DE MÓDULOS DEL SISTEMA
---------------------------------

MÓDULO 1: CATÁLOGO DE CUENTAS
  - Consulta del catálogo clasificado por naturaleza (Activo, Pasivo, Capital)
    y rubros contables (Activo Corriente, No Corriente, Pasivo Corriente,
    No Corriente, Patrimonio, Ingresos, Costos y Gastos).
  - Formulario para dar de alta cuentas nuevas con saldo habitual sugerido.

CICLO DE PARTIDAS: LIBRO DIARIO Y LIBRO MAYOR
  - Registro de asientos contables con validación de partida doble obligatoria
    y campo para glosa o concepto.
  - Visualización cronológica del Libro Diario con enlaces a asientos de Kardex.
  - Libro Mayor general con centralización automática de cargos, abonos y saldos.

MÓDULO 2: BALANZA DE COMPROBACIÓN Y ESTADOS FINANCIEROS (NUEVO)
  2.1. Balanza de Comprobación:
       - Recorre todas las cuentas activas del Libro Mayor.
       - Muestra para cada cuenta: Total Mov. Deudores (Debe), Total Mov.
         Acreedores (Haber), Saldo Deudor y Saldo Acreedor.
       - Fila de Totales con verificación en pantalla:
         ∑ Mov. Deudores = ∑ Mov. Acreedores y ∑ Saldos Deudores = ∑ Saldos Acreedores.
  2.2. Estado de Resultados (Pérdidas y Ganancias):
       - Filtra automáticamente cuentas de Ingresos por Ventas, Costos de
         Ventas y Gastos Operativos (Administración y Ventas).
       - Cálculo jerárquico:
         Utilidad Bruta = Ingresos por Ventas - Costo de Ventas
         Utilidad Operativa = Utilidad Bruta - Gastos Operativos
       - Alimenta automáticamente la cuenta de Patrimonio en el Balance General.
  2.3. Balance General (Estado de Situación Financiera):
       - Tres bloques clasificados:
         * ACTIVO: Corriente y No Corriente.
         * PASIVO: Corriente y No Corriente.
         * PATRIMONIO: Capital Social + Utilidades + Utilidad/Pérdida del Ejercicio.
       - Validación de la Ecuación Contable:
         Total Activo = Total Pasivo + Total Patrimonio.
         Alerta en consola y pantalla si existiese algún descuadre.

MÓDULO 3: VALUACIÓN Y CONTROL DE INVENTARIOS - KARDEX (NUEVO)
  Método de Valuación: Promedio Ponderado
  1. Estructura de Datos del Producto:
     - Código, Descripción, Existencia Actual (Unidades), Costo Unitario
       Promedio ($) y Valor Total de Inventario ($).
  2. Registro de Compras (Entradas):
     - Solicita cantidad comprada y precio unitario de compra.
     - Calcula el nuevo costo promedio ponderado:
       Nuevo Costo Promedio = (Valor Total Anterior + Costo Nueva Compra) /
                              (Existencia Anterior + Unidades Compradas)
     - Genera automáticamente la partida en el Diario:
       Cargo a Inventario/Mercancía y Crédito Fiscal IVA (13%), con abono a
       Efectivo/Bancos o Proveedores.
  3. Registro de Ventas (Salidas):
     - Aplica el costo promedio ponderado vigente para el Costo de Ventas.
     - Genera automáticamente el doble asiento contable:
       * Partida de Ingreso: Cobro al cliente (Bancos o Clientes) con abono
         a Ingresos por Ventas y Débito Fiscal IVA (13%).
       * Partida de Costo: Cargo a Costo de Ventas y abono a Inventario
         (calculado a costo promedio vigente).
  4. Tarjeta Kardex Detallada:
     - Tabla estándar con 3 bloques de columnas: ENTRADAS, SALIDAS y SALDOS,
       con enlaces de auditoría a las partidas generadas en el Libro Diario.

CASOS DE PRUEBA
---------------
  En la opción "Casos de prueba" del menú, puede cargar con un solo clic el
  "Caso Comercial con Kardex" para verificar inmediatamente todo el ciclo
  contable funcionando con datos reales y estados financieros cuadrados.
================================================================================
