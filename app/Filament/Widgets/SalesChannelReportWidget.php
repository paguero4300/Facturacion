<?php

namespace App\Filament\Widgets;

use App\Models\InvoiceDetail;
use Filament\Widgets\Widget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SalesChannelReportWidget extends BaseWidget
{
    protected static ?string $heading = 'Reporte de Canales de Venta';

    protected string $view = 'filament.widgets.sales-channel-report-widget';

    /**
     * Obtiene los datos agregados del reporte de canales de venta
     *
     * @return \Illuminate\Support\Collection
     */
    public function getReportData()
    {
        return InvoiceDetail::select([
                'invoices.document_type',
                'invoices.payment_method',
                DB::raw('SUM(invoice_details.quantity) as total_cantidad'),
                DB::raw('SUM(invoice_details.line_total) as total_venta'),
                DB::raw('COUNT(DISTINCT invoice_details.invoice_id) as total_documentos')
            ])
            ->join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', '!=', 'anulado')
            ->groupBy('invoices.document_type', 'invoices.payment_method')
            ->orderBy('total_venta', 'desc')
            ->get();
    }

    /**
     * Convierte códigos de documento a labels legibles
     *
     * @param string $type
     * @return string
     */
    public function getDocumentTypeLabel($type)
    {
        $labels = [
            '01' => 'Factura',
            '03' => 'Boleta',
            '07' => 'Nota de Crédito',
            '08' => 'Nota de Débito',
            '09' => 'Guía de Remisión',
            '12' => 'Ticket de Máquina Registradora',
            '13' => 'Documento No Domiciliado',
            '14' => 'Recibo por Honorarios',
            '15' => 'Recibo de Pago',
            '16' => 'Nota de Venta',
            '20' => 'Comprobante de Retención',
            '21' => 'Conocimiento de Embarque',
            '31' => 'Guía de Remisión - Transportista',
            '37' => 'Comprobante de Percepción',
            '40' => 'Comprobante de Pago SEAE',
            '41' => 'Comprobante de Pago SEAE - Venta Interna',
            '43' => 'Boleto de Transporte Terrestre',
            '45' => 'Documento de Atribución',
            '56' => 'Factura Guía',
            '71' => 'Guía de Remisión - Remitente',
            '72' => 'Recibo por Servicios Públicos',
            '87' => 'Nota de Crédito Especial',
            '88' => 'Nota de Débito Especial',
        ];

        return $labels[$type] ?? $type;
    }

    /**
     * Convierte códigos de método de pago a labels legibles
     *
     * @param string $method
     * @return string
     */
    public function getPaymentMethodLabel($method)
    {
        $labels = [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'credit' => 'Crédito',
            'check' => 'Cheque',
            'deposit' => 'Depósito',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'other' => 'Otro',
        ];

        return $labels[$method] ?? $method;
    }

    /**
     * Define el span de columnas para el widget
     *
     * @return int|string|array
     */
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    /**
     * Define el encabezado del widget
     *
     * @return string
     */
    public function getHeading(): string
    {
        return 'Reporte de Canales de Venta';
    }

    /**
     * Obtiene los datos formateados para la vista
     *
     * @return array
     */
    public function getFormattedData(): array
    {
        $data = $this->getReportData();
        $formatted = [];

        foreach ($data as $row) {
            $formatted[] = [
                'tipo_documento' => $this->getDocumentTypeLabel($row->document_type ?? ''),
                'tipo_documento_code' => $row->document_type ?? '',
                'metodo_pago' => $this->getPaymentMethodLabel($row->payment_method ?? ''),
                'metodo_pago_code' => $row->payment_method ?? '',
                'total_cantidad' => $row->total_cantidad ?? 0,
                'total_venta' => $row->total_venta ?? 0,
                'total_documentos' => $row->total_documentos ?? 0,
            ];
        }

        return $formatted;
    }

    /**
     * Obtiene el total general de ventas
     *
     * @return float
     */
    public function getTotalSales(): float
    {
        return $this->getReportData()->sum('total_venta');
    }

    /**
     * Obtiene el total general de documentos
     *
     * @return int
     */
    public function getTotalDocuments(): int
    {
        return $this->getReportData()->sum('total_documentos');
    }

    /**
     * Obtiene el total general de cantidad de productos
     *
     * @return float
     */
    public function getTotalQuantity(): float
    {
        return $this->getReportData()->sum('total_cantidad');
    }
}