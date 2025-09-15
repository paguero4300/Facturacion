<x-filament-panels::page>
    <style>
        /* POS Theme Variables - Auto Dark/Light */
        .pos-container {
            /* Light theme variables */
            --pos-bg-primary: rgb(255 255 255);
            --pos-bg-secondary: rgb(255 255 255);
            --pos-bg-tertiary: rgb(249 250 251);
            --pos-text-primary: rgb(17 24 39);
            --pos-text-secondary: rgb(75 85 99);
            --pos-text-muted: rgb(156 163 175);
            --pos-border: rgb(229 231 235);
            --pos-border-light: rgb(209 213 219);
            --pos-accent: rgb(99 102 241);
            --pos-accent-hover: rgb(79 70 229);
            --pos-success: rgb(16 185 129);
            --pos-danger: rgb(239 68 68);
        }
        
        /* Dark theme variables */
        .dark .pos-container {
            --pos-bg-primary: rgb(15 23 42);
            --pos-bg-secondary: rgb(30 41 59);
            --pos-bg-tertiary: rgb(51 65 85);
            --pos-text-primary: rgb(248 250 252);
            --pos-text-secondary: rgb(203 213 225);
            --pos-text-muted: rgb(100 116 139);
            --pos-border: rgb(51 65 85);
            --pos-border-light: rgb(71 85 105);
            --pos-accent: rgb(99 102 241);
            --pos-accent-hover: rgb(79 70 229);
            --pos-success: rgb(16 185 129);
            --pos-danger: rgb(239 68 68);
        }
        
        .pos-container {
            margin: 0;
            padding: 15px;
            display: flex;
            height: calc(100vh - 120px);
            background-color: #f5f5f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            gap: 15px;
        }
        
        .dark .pos-container {
            background-color: #0f172a;
        }
        
        .pos-container * {
            box-sizing: border-box;
        }
        
        /* Sidebar Styles */
        .pos-sidebar {
            width: 400px;
            background: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dark .pos-sidebar {
            background: #1e293b;
            border-color: #334155;
        }
        
        .product-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            min-height: 0;
            overflow: hidden;
        }
        
        .product-list {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .product-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 10px;
            padding: 10px 0;
            font-size: 11px;
            font-weight: 600;
            color: rgb(var(--gray-500));
            border-bottom: 1px solid rgb(var(--gray-200));
        }
        
        .dark .product-header {
            color: rgb(var(--gray-400));
            border-bottom-color: rgb(var(--gray-700));
        }
        
        .product-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgb(var(--gray-200));
        }
        
        .dark .product-item {
            border-bottom-color: rgb(var(--gray-700));
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 4px;
            color: rgb(var(--gray-900));
            font-size: 13px;
        }
        
        .dark .product-name {
            color: rgb(var(--gray-100));
        }
        
        .product-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            width: fit-content;
            text-transform: uppercase;
        }
        
        .dark .product-tag {
            background: #334155;
            color: #6366f1;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .qty-btn {
            width: 22px;
            height: 22px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        
        .dark .qty-btn {
            border-color: #475569;
            background: #334155;
            color: #f8fafc;
        }
        
        .qty-btn:hover {
            background: #f5f5f5;
            border-color: #6366f1;
        }
        
        .dark .qty-btn:hover {
            background: #475569;
            border-color: #6366f1;
        }
        
        .qty-value {
            min-width: 18px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            color: rgb(var(--gray-900));
        }
        
        .dark .qty-value {
            color: rgb(var(--gray-100));
        }
        
        .price, .subtotal {
            font-weight: 600;
            color: rgb(var(--gray-900));
            font-size: 13px;
        }
        
        .dark .price, .dark .subtotal {
            color: rgb(var(--gray-100));
        }
        
        .remove-btn {
            width: 18px;
            height: 18px;
            border: none;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        
        .remove-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        .sidebar-fixed-bottom {
            margin-top: auto;
            background: white;
            border-top: 1px solid #e5e5e7;
            padding-top: 15px;
        }
        
        .dark .sidebar-fixed-bottom {
            background: #1e293b;
            border-top-color: #334155;
        }
        

        
        .totals-section {
            margin-bottom: 20px;
            padding: 15px;
            border-top: 2px solid #e5e5e7;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            margin-top: 15px;
        }
        
        .dark .totals-section {
            border-top-color: #334155;
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 13px;
            border-bottom: 1px solid rgb(var(--gray-200));
            line-height: 1.2;
        }
        
        .dark .total-row {
            border-bottom-color: rgb(var(--gray-700));
        }
        
        .total-row:last-child {
            border-bottom: none;
        }
        
        .total-label {
            color: rgb(var(--gray-600));
            font-weight: 500;
            font-size: 12px;
            text-align: left;
        }
        
        .dark .total-label {
            color: rgb(var(--gray-400));
        }
        
        .total-value {
            font-weight: 700;
            color: #2d3748;
            font-size: 14px;
            background: #f7fafc;
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            text-align: right;
            min-width: 80px;
        }
        
        .dark .total-value {
            color: #f8fafc;
            background: #0f172a;
            border-color: #475569;
        }
        
        .total-row.final {
            font-weight: 700;
            font-size: 15px;
            border-top: 2px solid #6366f1;
            padding-top: 10px;
            margin-top: 10px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin: 10px -15px -15px -15px;
            line-height: 1.3;
        }
        
        .total-row.final .total-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            text-align: left;
        }
        
        .total-row.final .total-value {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 16px;
            font-weight: 800;
            text-align: right;
            min-width: 90px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-direction: row;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgb(var(--gray-200));
        }
        
        .dark .action-buttons {
            border-top-color: rgb(var(--gray-700));
        }
        
        .action-btn {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .action-btn:active {
            transform: translateY(0);
        }
        
        .clear-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: 2px solid transparent;
        }
        
        .clear-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        .pay-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 2px solid transparent;
        }
        
        .pay-btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .action-btn:disabled:hover {
            transform: none;
        }
        

        
        /* Main Content Styles */
        .pos-main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dark .pos-main-content {
            background: #1e293b;
            border-color: #334155;
        }
        
        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            border: none;
            background: #f0f0f0;
            color: #666;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .dark .tab {
            background: #334155;
            color: #cbd5e1;
        }
        
        .tab:hover {
            background: #e5e5e5;
            color: #333;
        }
        
        .dark .tab:hover {
            background: #475569;
            color: #f8fafc;
        }
        
        .tab.active {
            background: #6366f1;
            color: white;
        }
        

        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dark .product-card {
            background: #334155;
            border-color: #475569;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            border-color: #6366f1;
        }
        
        .product-image {
            position: relative;
            height: 150px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .dark .product-image {
            background: #1e293b;
        }
        
        .pos-container .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .price-tag {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #6366f1;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .weight-tag {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .product-details {
            padding: 15px;
        }
        
        .pos-container .product-details h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
            color: rgb(var(--gray-900));
        }
        
        .dark .pos-container .product-details h3 {
            color: rgb(var(--gray-100));
        }
        
        .pos-container .product-details p {
            font-size: 12px;
            color: rgb(var(--gray-500));
            font-family: monospace;
        }
        
        .dark .pos-container .product-details p {
            color: rgb(var(--gray-400));
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: rgb(var(--gray-500));
        }
        
        .dark .empty-cart {
            color: rgb(var(--gray-400));
        }
        
        .empty-cart p {
            color: rgb(var(--gray-600));
            margin: 8px 0;
        }
        
        .dark .empty-cart p {
            color: rgb(var(--gray-300));
        }
        
        .empty-cart small {
            color: rgb(var(--gray-500));
        }
        
        .dark .empty-cart small {
            color: rgb(var(--gray-400));
        }
        
        .empty-cart-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .pos-container {
                flex-direction: column;
                height: auto;
                padding: 10px;
                gap: 10px;
            }
            
            .pos-sidebar {
                width: 100%;
                height: auto;
                border-radius: 12px;
            }
            
            .pos-main-content {
                border-radius: 12px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .action-btn {
                padding: 12px 16px;
                font-size: 14px;
            }
            
            .action-buttons {
                gap: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .category-tabs {
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 360px) {
            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .action-btn {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
        
        /* Payment Modal Styles */
        .payment-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-modal-content {
            background: white;
            border-radius: 16px;
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }
        
        .dark .payment-modal-content {
            background: #1e293b;
            color: #f8fafc;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .payment-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e5e5e7;
        }
        
        .dark .payment-modal-header {
            border-bottom-color: #334155;
        }
        
        .payment-modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .dark .payment-modal-header h3 {
            color: #f8fafc;
        }
        
        .payment-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .payment-modal-close:hover {
            background: #f5f5f5;
            color: #333;
        }
        
        .dark .payment-modal-close {
            color: #cbd5e1;
        }
        
        .dark .payment-modal-close:hover {
            background: #334155;
            color: #f8fafc;
        }
        
        .payment-modal-body {
            padding: 32px;
        }
        
        /* Payment Modal Layout */
        .payment-modal-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 32px;
            min-height: 400px;
        }
        
        .payment-form-column {
            flex: 1;
        }
        
        .payment-totals-column {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e5e5e7;
            height: fit-content;
            position: sticky;
            top: 0;
            min-width: 400px;
        }
        
        .dark .payment-totals-column {
            background: #334155;
            border-color: #475569;
        }
        
        .totals-header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #6366f1;
        }
        
        .totals-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .dark .totals-header h4 {
            color: #f8fafc;
        }
        
        .payment-summary-right {
            margin-bottom: 20px;
        }
        
        .payment-summary-right .payment-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #e5e5e7;
        }
        
        .dark .payment-summary-right .payment-summary-row {
            border-bottom-color: #475569;
        }
        
        .payment-summary-right .payment-summary-row:last-child {
            border-bottom: none;
        }
        
        .payment-summary-right .payment-total {
            border-top: 2px solid #6366f1;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 700;
            font-size: 16px;
            color: #6366f1;
        }
        
        .total-amount-right {
            font-size: 18px;
            font-weight: 800;
        }
        
        .cash-calculations {
            border-top: 1px solid #e5e5e7;
            padding-top: 16px;
        }
        
        .dark .cash-calculations {
            border-top-color: #475569;
        }
        
        .calculations-header {
            margin-bottom: 12px;
        }
        
        .calculations-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #666;
        }
        
        .dark .calculations-header h5 {
            color: #cbd5e1;
        }
        
        .change-display-right {
            background: white;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e5e5e7;
        }
        
        .dark .change-display-right {
            background: #1e293b;
            border-color: #475569;
        }
        
        .change-display-right .change-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
        }
        
        .change-display-right .change-final {
            border-top: 2px solid #10b981;
            padding-top: 8px;
            margin-top: 6px;
            font-weight: 700;
            font-size: 14px;
        }
        
        .change-display-right .change-final .change-amount {
            color: #10b981;
            font-size: 16px;
        }
        
        .change-display-right .change-final .change-amount.negative {
            color: #ef4444;
        }
        
        .payment-form-group {
            margin-bottom: 24px;
        }
        
        .payment-form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .dark .payment-form-group label {
            color: #f8fafc;
        }
        
        .payment-form-group select,
        .payment-form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #333;
            transition: all 0.2s ease;
        }
        
        .dark .payment-form-group select,
        .dark .payment-form-group input {
            background: #334155;
            border-color: #475569;
            color: #f8fafc;
        }
        
        .payment-form-group select:focus,
        .payment-form-group input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .payment-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
        }
        
        .dark .payment-summary {
            background: #334155;
        }
        
        .payment-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .payment-summary-row.payment-total {
            border-top: 2px solid #6366f1;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 700;
            font-size: 16px;
            color: #6366f1;
        }
        
        .payment-modal-footer {
            display: flex;
            gap: 16px;
            padding: 20px;
            border-top: 1px solid #e5e5e7;
        }
        
        .dark .payment-modal-footer {
            border-top-color: #334155;
        }
        
        .payment-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .payment-btn-cancel {
            background: #f5f5f5;
            color: #666;
        }
        
        .payment-btn-cancel:hover {
            background: #e5e5e5;
            color: #333;
        }
        
        .dark .payment-btn-cancel {
            background: #475569;
            color: #cbd5e1;
        }
        
        .dark .payment-btn-cancel:hover {
            background: #334155;
            color: #f8fafc;
        }
        
        .payment-btn-confirm {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .payment-btn-confirm:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
        }
        
        .payment-btn-print {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }
        
        .payment-btn-print:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-1px);
        }
        
        /* Cash Payment Styles */
        .cash-payment-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .dark .cash-payment-section {
            background: #334155;
        }
        
        .amount-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .currency-symbol {
            position: absolute;
            left: 12px;
            font-weight: 600;
            color: #666;
            z-index: 1;
        }
        
        .dark .currency-symbol {
            color: #cbd5e1;
        }
        
        .amount-input {
            padding-left: 35px !important;
            font-size: 18px !important;
            font-weight: 600;
            text-align: right;
        }
        
        .change-display {
            margin: 20px 0;
            padding: 16px;
            background: white;
            border-radius: 10px;
            border: 2px solid #e5e5e7;
        }
        
        .dark .change-display {
            background: #1e293b;
            border-color: #475569;
        }
        
        .change-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 14px;
        }
        
        .change-row.change-final {
            border-top: 2px solid #10b981;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 700;
            font-size: 16px;
        }
        
        .change-label {
            color: #666;
        }
        
        .dark .change-label {
            color: #cbd5e1;
        }
        
        .change-amount {
            font-weight: 600;
            color: #333;
        }
        
        .dark .change-amount {
            color: #f8fafc;
        }
        
        .change-final .change-amount {
            color: #10b981;
            font-size: 18px;
        }
        
        .change-final .change-amount.negative {
            color: #ef4444;
        }
        
        .quick-amounts {
            margin-top: 20px;
        }
        
        .quick-amounts-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .dark .quick-amounts-label {
            color: #cbd5e1;
        }
        
        .quick-amounts-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        
        .quick-amount-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .dark .quick-amount-btn {
            background: #475569;
            border-color: #64748b;
            color: #f8fafc;
        }
        
        .quick-amount-btn:hover {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }
        
        .quick-amount-btn:active {
            transform: scale(0.98);
        }
        
        /* Client Search Styles */
        .client-search-section {
            margin-bottom: 16px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e5e5e7;
        }
        
        .dark .client-search-section {
            background: #334155;
            border-color: #475569;
        }
        
        .client-search-section label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 0 !important;
        }
        
        .client-search-section input[type="checkbox"] {
            width: auto !important;
            margin: 0;
        }
        
        .search-client-btn {
            width: 100%;
            padding: 10px 16px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .search-client-btn:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-1px);
        }
        
        .search-client-btn:active {
            transform: translateY(0);
        }
        
        /* Digital Payment Styles */
        .digital-payment-section {
            background: #f0f9ff;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #0ea5e9;
        }
        
        .dark .digital-payment-section {
            background: #1e293b;
            border-color: #0ea5e9;
        }
        
        .digital-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #333;
            transition: all 0.2s ease;
        }
        
        .dark .digital-input {
            background: #334155;
            border-color: #0ea5e9;
            color: #f8fafc;
        }
        
        .digital-input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        .digital-payment-info {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 16px;
            padding: 12px;
            background: #fef3c7;
            border-radius: 8px;
            border: 1px solid #f59e0b;
        }
        
        .dark .digital-payment-info {
            background: #451a03;
            border-color: #f59e0b;
        }
        
        .info-icon {
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .info-text {
            font-size: 13px;
            color: #92400e;
            line-height: 1.4;
        }
        
        .dark .info-text {
            color: #fbbf24;
        }
        
        /* Digital Payment Info Right */
        .digital-payment-info-right {
            border-top: 1px solid #e5e5e7;
            padding-top: 16px;
        }
        
        .dark .digital-payment-info-right {
            border-top-color: #475569;
        }
        
        .digital-info-display {
            background: #f0f9ff;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #0ea5e9;
        }
        
        .dark .digital-info-display {
            background: #1e293b;
            border-color: #0ea5e9;
        }
        
        .digital-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .info-label {
            color: #0369a1;
        }
        
        .dark .info-label {
            color: #38bdf8;
        }
        
        .info-amount {
            color: #0369a1;
            font-size: 16px;
            font-weight: 700;
        }
        
        .dark .info-amount {
            color: #38bdf8;
        }
        
        .digital-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #bae6fd;
        }
        
        .dark .digital-status {
            border-top-color: #0369a1;
        }
        
        .status-icon {
            font-size: 14px;
        }
        
        .status-text {
            font-size: 12px;
            color: #0369a1;
            font-weight: 500;
        }
        
        .dark .status-text {
            color: #38bdf8;
        }
        
        /* Digital Payment Compact Styles */
        .digital-payment-section-compact {
            background: #f0f9ff;
            border-radius: 8px;
            padding: 12px;
            margin-top: 16px;
            border: 1px solid #0ea5e9;
        }
        
        .dark .digital-payment-section-compact {
            background: #1e293b;
            border-color: #0ea5e9;
        }
        
        .digital-fields-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .digital-fields-row .payment-form-group {
            margin-bottom: 0;
        }
        
        .digital-fields-row label {
            font-size: 12px !important;
            margin-bottom: 4px !important;
            font-weight: 500;
        }
        
        .digital-input-compact {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            font-size: 13px;
            background: white;
            color: #333;
            transition: all 0.2s ease;
        }
        
        .dark .digital-input-compact {
            background: #334155;
            border-color: #0ea5e9;
            color: #f8fafc;
        }
        
        .digital-input-compact:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.1);
        }
        
        .digital-payment-info-compact {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 8px;
            background: #fef3c7;
            border-radius: 6px;
            border: 1px solid #f59e0b;
        }
        
        .dark .digital-payment-info-compact {
            background: #451a03;
            border-color: #f59e0b;
        }
        
        .info-icon-small {
            font-size: 12px;
            flex-shrink: 0;
        }
        
        .info-text-small {
            font-size: 11px;
            color: #92400e;
            line-height: 1.3;
        }
        
        .dark .info-text-small {
            color: #fbbf24;
        }
        
        /* Responsive para campos compactos */
        @media (max-width: 768px) {
            .digital-fields-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
        
        /* Ticket Modal Styles */
        .ticket-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .ticket-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
            max-height: 90vh;
            overflow-y: auto;
            animation: ticketSlideIn 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        @keyframes ticketSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .ticket-content {
            width: 100%;
            max-width: none;
            background: white;
            padding: 24px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #2d3748;
        }
        
        .ticket-header {
            text-align: center;
            margin-bottom: 16px;
            padding: 12px 0;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .company-ruc {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #64748b;
        }
        
        .company-address,
        .company-phone,
        .company-email {
            font-size: 12px;
            margin-bottom: 3px;
            color: #64748b;
            font-weight: 400;
        }
        
        .ticket-divider {
            text-align: center;
            margin: 12px 0;
            font-size: 12px;
            letter-spacing: 1px;
            color: #cbd5e1;
            font-weight: 300;
        }
        
        .ticket-document-info {
            text-align: center;
            margin: 16px 0;
            padding: 12px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .document-type {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .document-number {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }
        
        .document-date {
            font-size: 12px;
            color: #6b7280;
            font-weight: 400;
        }
        
        .ticket-client-info {
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .client-name,
        .client-document {
            font-size: 12px;
            margin-bottom: 3px;
            color: #4f46e5;
            font-weight: 500;
        }
        
        .ticket-products {
            margin: 16px 0;
            padding: 0;
        }
        
        .products-header {
            display: grid;
            grid-template-columns: 3fr 1fr 1.2fr 1.2fr;
            gap: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            padding: 8px 0;
            border-bottom: 2px solid #e2e8f0;
            text-align: center;
            color: #374151;
        }
        
        .products-header .col-desc {
            text-align: left;
        }
        
        .products-list {
            margin-bottom: 12px;
        }
        
        .product-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1.2fr 1.2fr;
            gap: 12px;
            font-size: 11px;
            margin-bottom: 6px;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-desc {
            word-wrap: break-word;
            line-height: 1.1;
            text-align: left;
            overflow: hidden;
        }
        
        .product-qty {
            text-align: center;
            font-weight: bold;
        }
        
        .product-price,
        .product-total {
            text-align: right;
            font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
            font-weight: 500;
        }
        
        .product-total {
            font-weight: bold;
        }
        
        .ticket-totals {
            margin: 16px 0;
            padding: 12px 0;
            border-top: 2px solid #e2e8f0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 500;
        }
        
        .total-row .total-value {
            font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
            font-weight: 600;
            color: #1f2937;
        }
        
        .total-row.total-final {
            font-weight: 700;
            font-size: 16px;
            border-top: 2px solid #2563eb;
            padding-top: 8px;
            margin-top: 8px;
            color: #2563eb;
        }
        
        .total-row.total-final .total-value {
            color: #2563eb;
            font-weight: 700;
        }
        
        .ticket-payment-info {
            margin: 12px 0;
            padding: 8px 0;
            border-top: 1px solid #e2e8f0;
        }
        
        .payment-method,
        .payment-received,
        .payment-change,
        .payment-reference {
            font-size: 12px;
            margin-bottom: 4px;
            color: #059669;
            font-weight: 500;
        }
        
        .payment-change {
            font-weight: 600;
            color: #2563eb;
        }
        
        .ticket-footer {
            text-align: center;
            margin-top: 12px;
            padding: 8px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .footer-text {
            font-size: 12px;
            margin-bottom: 3px;
            color: #475569;
            font-weight: 600;
        }
        
        .footer-date {
            font-size: 10px;
            margin-top: 6px;
            color: #64748b;
            font-style: italic;
        }
        
        .ticket-actions {
            display: flex;
            gap: 12px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            justify-content: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .ticket-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ticket-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .ticket-btn:active {
            transform: translateY(0);
        }
        
        .ticket-btn-print {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 2px solid transparent;
        }
        
        .ticket-btn-print:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-color: #047857;
        }
        
        .ticket-btn-close {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            border: 2px solid transparent;
        }
        
        .ticket-btn-close:hover {
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            border-color: #374151;
        }
        
        /* Print styles */
        @media print {
            .ticket-modal {
                position: static;
                background: none;
                padding: 0;
            }
            
            .ticket-container {
                box-shadow: none;
                max-height: none;
            }
            
            .ticket-actions {
                display: none;
            }
            
            .ticket-content {
                padding: 0;
                margin: 0;
            }
        }
        
        /* Form Layout Improvements */
        .payment-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .payment-form-row .payment-form-group {
            margin-bottom: 0;
        }
        
        /* Responsive Modal */
        @media (max-width: 1200px) {
            .payment-modal-content {
                max-width: 800px;
            }
            
            .payment-modal-layout {
                grid-template-columns: 1fr 350px;
                gap: 24px;
            }
        }
        
        @media (max-width: 1024px) {
            .payment-modal-content {
                max-width: 700px;
            }
            
            .payment-modal-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .payment-totals-column {
                order: -1;
                position: static;
                min-width: auto;
            }
        }
        
        @media (max-width: 768px) {
            .payment-modal-content {
                width: 98%;
                margin: 5px;
            }
            
            .payment-modal-header,
            .payment-modal-body,
            .payment-modal-footer {
                padding: 16px;
            }
            
            .payment-form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .quick-amounts-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-amount-btn {
                font-size: 11px;
                padding: 6px 8px;
            }
            
            .payment-modal-layout {
                gap: 12px;
            }
        }

    </style>
    
    <!-- POS Layout basado en el diseño de referencia -->
    <div class="pos-container">
        <!-- Sidebar Izquierdo: Carrito -->
        <div class="pos-sidebar">
            <!-- Sección de Productos en el Carrito -->
            <div class="product-section">
                <div class="product-header">
                    <span class="product-label">PRODUCTO</span>
                    <span class="qty-label">CANT</span>
                    <span class="price-label">PRECIO</span>
                    <span class="subtotal-label">SUBTOTAL</span>
                </div>
                
                <div class="product-list">
                    @if(!empty($data['cart_items']))
                        @foreach($data['cart_items'] as $index => $item)
                            @php
                                $product = \App\Models\Product::find($item['product_id']);
                            @endphp
                            <div class="product-item">
                                <div class="product-info">
                                    <span class="product-name">{{ $item['description'] }}</span>
                                    <span class="product-tag">{{ $product && $product->category ? $product->category->name : 'PRODUCTO' }}</span>
                                </div>
                                <div class="quantity-controls">
                                    <button class="qty-btn minus" wire:click="decreaseQuantity({{ $index }})">-</button>
                                    <span class="qty-value">{{ $item['quantity'] }}</span>
                                    <button class="qty-btn plus" wire:click="increaseQuantity({{ $index }})">+</button>
                                </div>
                                <div class="price">S/ {{ number_format($item['unit_price'], 2) }}</div>
                                <div class="subtotal">S/ {{ number_format($item['total'], 2) }}</div>
                                <button class="remove-btn" wire:click="removeFromCart({{ $index }})">×</button>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-cart">
                            <div class="empty-cart-icon">🛒</div>
                            <p>Carrito vacío</p>
                            <small>Agrega productos para comenzar</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección Fija Inferior -->
            <div class="sidebar-fixed-bottom">
                <!-- Sección de Totales -->
                <div class="totals-section">
                    <div class="total-row">
                        <span class="total-label">Total CANT</span>
                        <span class="total-value">{{ array_sum(array_column($data['cart_items'] ?? [], 'quantity')) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Sub Total</span>
                        <span class="total-value">S/ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">IGV (18%)</span>
                        <span class="total-value">S/ {{ number_format($igv, 2) }}</span>
                    </div>
                    <div class="total-row final">
                        <span class="total-label">TOTAL</span>
                        <span class="total-value">S/ {{ number_format($total, 2) }}</span>
                    </div>
                </div>
                
                <!-- Botones de Acción -->
                <div class="action-buttons">
                    <button class="action-btn clear-btn" wire:click="clearCart">
                        🗑️ Limpiar Carrito
                    </button>
                    <button class="action-btn pay-btn" onclick="openPaymentModal()" {{ empty($data['cart_items']) ? 'disabled' : '' }}>
                        💳 Pagar
                    </button>
                </div>
            </div>


        </div>

        <!-- Contenido Principal: Catálogo -->
        <div class="pos-main-content">
            <!-- Tabs de Categorías -->
            <div class="category-tabs">
                <button class="tab {{ is_null($selectedCategory) ? 'active' : '' }}" wire:click="selectCategory(null)">
                    Todas las Categorías
                </button>
                @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                    <button class="tab {{ $selectedCategory == $category->id ? 'active' : '' }}" wire:click="selectCategory({{ $category->id }})">
                        {{ strtoupper($category->name) }}
                    </button>
                @endforeach
            </div>



            <!-- Grilla de Productos -->
            <div class="products-grid">
                @foreach(\App\Models\Product::query()->active()->forSale()->when($selectedCategory, fn($q) => $q->where('category_id', $selectedCategory))->get() as $product)
                    <div class="product-card" wire:click="addToCart({{ $product->id }})">
                        <div class="product-image">
                            <img 
                                src="{{ $product->image_path ? Storage::disk('public')->url($product->image_path) : '/images/no-image.svg' }}" 
                                alt="{{ $product->name }}"
                            >
                            <div class="price-tag">S/ {{ number_format($product->sale_price, 2) }}</div>
                            <div class="weight-tag">{{ $product->current_stock }} {{ $product->unit_code ?? 'unidad' }}</div>
                        </div>
                        <div class="product-details">
                            <h3>{{ $product->name }}</h3>
                            <p>{{ $product->code }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Modal de Pago -->
    <div id="payment-modal" class="payment-modal" style="display: none;">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <h3>Procesar Pago</h3>
                <button class="payment-modal-close" onclick="closePaymentModal()">&times;</button>
            </div>
            
            <div class="payment-modal-body">
                <div class="payment-modal-layout">
                    <!-- Columna Izquierda: Formulario -->
                    <div class="payment-form-column">
                        <form id="payment-form">
                            <!-- Primera fila: Tipo de documento y Método de pago -->
                            <div class="payment-form-row">
                                <div class="payment-form-group">
                                    <label for="document_type">Tipo de Documento *</label>
                                    <select id="document_type" name="document_type" required onchange="toggleClientFields()">
                                        <option value="09">Nota de Venta</option>
                                        <option value="03">Boleta de Venta</option>
                                        <option value="01">Factura</option>
                                    </select>
                                </div>
                                
                                <div class="payment-form-group">
                                    <label for="payment_method">Método de Pago *</label>
                                    <select id="payment_method" name="payment_method" required onchange="toggleCashFields()">
                                        <option value="cash">💵 Efectivo</option>
                                        <option value="card">💳 Tarjeta</option>
                                        <option value="yape">📱 Yape</option>
                                        <option value="plin">📲 Plin</option>
                                        <option value="transfer">🏦 Transferencia</option>
                                        <option value="other">🔄 Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Campos de Cliente -->
                            <div id="client-fields">
                                <div class="client-search-section">
                                    <div class="payment-form-group">
                                        <label>
                                            <input type="checkbox" id="use_specific_client" name="use_specific_client" onchange="toggleSpecificClient()">
                                            Especificar cliente (opcional para Boleta/Nota de Venta)
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="specific-client-fields" style="display: none;">
                                    <div class="payment-form-row">
                                        <div class="payment-form-group">
                                            <label for="client_document_type">Tipo Doc. Cliente *</label>
                                            <select id="client_document_type" name="client_document_type">
                                                <option value="6">RUC</option>
                                                <option value="1">DNI</option>
                                                <option value="4">Carnet de Extranjería</option>
                                                <option value="7">Pasaporte</option>
                                            </select>
                                        </div>
                                        
                                        <div class="payment-form-group">
                                            <label for="client_document_number">Número de Documento *</label>
                                            <input type="text" id="client_document_number" name="client_document_number" placeholder="Ej: 20123456789">
                                        </div>
                                    </div>
                                    
                                    <div class="payment-form-group">
                                        <label for="client_business_name">Razón Social / Nombre *</label>
                                        <input type="text" id="client_business_name" name="client_business_name" placeholder="Nombre del cliente">
                                    </div>
                                    
                                    <!-- Botón para buscar cliente existente -->
                                    <div class="payment-form-group">
                                        <button type="button" class="search-client-btn" onclick="searchExistingClient()">
                                            🔍 Buscar Cliente Existente
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Campos de Efectivo (solo monto recibido y botones rápidos) -->
                            <div id="cash-fields" class="cash-payment-section">
                                <div class="payment-form-group">
                                    <label for="amount_received">Monto Recibido *</label>
                                    <div class="amount-input-container">
                                        <span class="currency-symbol">S/</span>
                                        <input type="number" id="amount_received" name="amount_received" 
                                               placeholder="0.00" step="0.01" min="0" 
                                               oninput="calculateChange()" class="amount-input">
                                    </div>
                                </div>
                                
                                <!-- Botones de Montos Rápidos -->
                                <div class="quick-amounts">
                                    <span class="quick-amounts-label">Montos rápidos:</span>
                                    <div class="quick-amounts-buttons" id="quick-amounts-container">
                                        <!-- Los botones se generarán dinámicamente con JavaScript -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Campos para Yape/Plin -->
                            <div id="digital-payment-fields" class="digital-payment-section-compact" style="display: none;">
                                <div class="digital-fields-row">
                                    <div class="payment-form-group">
                                        <label for="digital_reference">Referencia</label>
                                        <input type="text" id="digital_reference" name="digital_reference" 
                                               placeholder="Número de operación" class="digital-input-compact">
                                    </div>
                                    
                                    <div class="payment-form-group">
                                        <label for="digital_phone">Teléfono</label>
                                        <input type="text" id="digital_phone" name="digital_phone" 
                                               placeholder="987654321" class="digital-input-compact">
                                    </div>
                                </div>
                                
                                <div class="digital-payment-info-compact">
                                    <span class="info-icon-small">ℹ️</span>
                                    <span class="info-text-small">Verificar pago recibido antes de procesar</span>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Columna Derecha: Totales -->
                    <div class="payment-totals-column">
                        <div class="totals-header">
                            <h4>Resumen de Venta</h4>
                        </div>
                        
                        <!-- Resumen de Totales (siempre visible) -->
                        <div class="payment-summary-right">
                            <div class="payment-summary-row">
                                <span>Subtotal:</span>
                                <span>S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="payment-summary-row">
                                <span>IGV (18%):</span>
                                <span>S/ {{ number_format($igv, 2) }}</span>
                            </div>
                            <div class="payment-summary-row payment-total">
                                <span>TOTAL:</span>
                                <span class="total-amount-right">S/ {{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                        
                        <!-- Cálculos de Efectivo -->
                        <div id="cash-calculations" class="cash-calculations" style="display: none;">
                            <div class="calculations-header">
                                <h5>Cálculo de Vuelto</h5>
                            </div>
                            <div class="change-display-right">
                                <div class="change-row">
                                    <span class="change-label">Total a Pagar:</span>
                                    <span class="change-amount total-amount" id="modal-total-display">S/ 0.00</span>
                                </div>
                                <div class="change-row">
                                    <span class="change-label">Monto Recibido:</span>
                                    <span class="change-amount received-amount">S/ 0.00</span>
                                </div>
                                <div class="change-row change-final">
                                    <span class="change-label">Vuelto:</span>
                                    <span class="change-amount change-value">S/ 0.00</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información de Pago Digital -->
                        <div id="digital-payment-info" class="digital-payment-info-right" style="display: none;">
                            <div class="calculations-header">
                                <h5 id="digital-payment-title">Pago Digital</h5>
                            </div>
                            <div class="digital-info-display">
                                <div class="digital-info-row">
                                    <span class="info-label">Total a Pagar:</span>
                                    <span class="info-amount total-amount-digital">S/ 0.00</span>
                                </div>
                                <div class="digital-status">
                                    <div class="status-icon">✅</div>
                                    <div class="status-text">Verificar pago recibido</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="payment-modal-footer">
                <button type="button" class="payment-btn payment-btn-cancel" onclick="closePaymentModal()">
                    ❌ Cancelar
                </button>
                <button type="button" class="payment-btn payment-btn-confirm" onclick="confirmPayment()">
                    💳 Procesar Pago
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal de Ticket de Impresión -->
    <div id="ticket-modal" class="ticket-modal" style="display: none;">
        <div class="ticket-container">
            <div class="ticket-content" id="ticket-content">
                <!-- Header de la empresa -->
                <div class="ticket-header">
                    <div class="company-name">{{ $company?->business_name ?? 'MI EMPRESA' }}</div>
                    <div class="company-ruc">RUC: {{ $company?->ruc ?? '00000000000' }}</div>
                    <div class="company-address">{{ $company?->address ?? 'Dirección no configurada' }}, {{ $company?->district ?? 'Lima' }} - {{ $company?->province ?? 'Lima' }}</div>
                    @if($company?->phone)
                        <div class="company-phone">Tel: {{ $company->phone }}</div>
                    @endif
                    @if($company?->email)
                        <div class="company-email">Email: {{ $company->email }}</div>
                    @endif
                </div>
                
                <div class="ticket-divider">═══════════════════════════════</div>
                
                <!-- Información del documento -->
                <div class="ticket-document-info">
                    <div class="document-type" id="ticket-document-type">NOTA DE VENTA</div>
                    <div class="document-number" id="ticket-document-number">NV01-00000001</div>
                    <div class="document-date" id="ticket-document-date">08/09/2025 - 20:30</div>
                </div>
                
                <div class="ticket-divider">═══════════════════════════════</div>
                
                <!-- Información del cliente -->
                <div class="ticket-client-info" id="ticket-client-info">
                    <div class="client-name">Cliente: CLIENTE VARIOS</div>
                    <div class="client-document">DNI: 00000000</div>
                </div>
                
                <div class="ticket-divider">═══════════════════════════════</div>
                
                <!-- Productos -->
                <div class="ticket-products">
                    <div class="products-header">
                        <span class="col-desc">DESCRIPCIÓN</span>
                        <span class="col-qty">CANT</span>
                        <span class="col-price">P.UNIT</span>
                        <span class="col-total">TOTAL</span>
                    </div>
                    <div class="products-list" id="ticket-products-list">
                        <!-- Los productos se llenarán dinámicamente -->
                    </div>
                </div>
                
                <div class="ticket-divider">═══════════════════════════════</div>
                
                <!-- Totales -->
                <div class="ticket-totals">
                    <div class="total-row">
                        <span class="total-label">SUBTOTAL:</span>
                        <span class="total-value" id="ticket-subtotal">S/ 0.00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">IGV (18%):</span>
                        <span class="total-value" id="ticket-igv">S/ 0.00</span>
                    </div>
                    <div class="total-row total-final">
                        <span class="total-label">TOTAL:</span>
                        <span class="total-value" id="ticket-total">S/ 0.00</span>
                    </div>
                </div>
                
                <div class="ticket-divider">═══════════════════════════════</div>
                
                <!-- Información de pago -->
                <div class="ticket-payment-info">
                    <div class="payment-method" id="ticket-payment-method">Método: EFECTIVO</div>
                    <div class="payment-received" id="ticket-payment-received" style="display: none;">Recibido: S/ 0.00</div>
                    <div class="payment-change" id="ticket-payment-change" style="display: none;">Vuelto: S/ 0.00</div>
                    <div class="payment-reference" id="ticket-payment-reference" style="display: none;">Ref: 123456789</div>
                </div>
                

            </div>
            
            <!-- Botones del ticket -->
            <div class="ticket-actions">
                <button type="button" class="ticket-btn ticket-btn-print" onclick="printTicket()">
                    🖨️ Imprimir
                </button>
                <button type="button" class="ticket-btn ticket-btn-close" onclick="closeTicketModal()">
                    ✖️ Cerrar
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Datos de la empresa desde el backend
        const companyData = {
            business_name: @json($company?->business_name ?? 'MI EMPRESA'),
            ruc: @json($company?->ruc ?? '00000000000'),
            address: @json($company?->address ?? 'Dirección no configurada'),
            district: @json($company?->district ?? 'Lima'),
            province: @json($company?->province ?? 'Lima'),
            phone: @json($company?->phone),
            email: @json($company?->email)
        };
        
        let totalAmount = 0;
        
        // Función para obtener el total actual
        function getCurrentTotal() {
            // Intentar obtener desde Livewire primero
            if (window.Livewire && window.Livewire.find) {
                try {
                    const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    if (component) {
                        const total = component.get('total') || component.total || 0;
                        console.log('Total from Livewire component:', total);
                        if (total > 0) {
                            return parseFloat(total);
                        }
                    }
                } catch (e) {
                    console.log('No se pudo obtener total desde Livewire:', e);
                }
            }
            
            // Fallback: obtener desde el elemento DOM
            const totalElement = document.querySelector('.total-row.final .total-value');
            if (totalElement) {
                const totalText = totalElement.textContent.replace('S/', '').replace(',', '').trim();
                const total = parseFloat(totalText) || 0;
                console.log('Total obtenido del DOM:', totalText, '->', total);
                return total;
            }
            
            console.log('No se pudo obtener el total');
            return 0;
        }
        
        // Inicializar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                totalAmount = getCurrentTotal();
                console.log('Total inicializado:', totalAmount);
            }, 100);
        });
        
        // Actualizar total cuando cambie desde Livewire
        document.addEventListener('livewire:updated', function () {
            setTimeout(() => {
                totalAmount = getCurrentTotal();
                console.log('Total actualizado:', totalAmount);
            }, 50);
        });
        
        function openPaymentModal() {
            // Actualizar el total amount con el valor actual
            totalAmount = getCurrentTotal();
            console.log('Total al abrir modal:', totalAmount);
            
            if (totalAmount <= 0) {
                alert('No hay productos en el carrito o el total es 0');
                return;
            }
            
            document.getElementById('payment-modal').style.display = 'flex';
            // Reset form
            document.getElementById('payment-form').reset();
            document.getElementById('document_type').value = '09';
            document.getElementById('payment_method').value = 'cash';
            toggleClientFields();
            toggleCashFields();
            generateQuickAmounts();
            calculateChange();
            updateConfirmButton();
        }
        
        function closePaymentModal() {
            document.getElementById('payment-modal').style.display = 'none';
        }
        
        function toggleClientFields() {
            const documentType = document.getElementById('document_type').value;
            const clientFields = document.getElementById('client-fields');
            const useSpecificClient = document.getElementById('use_specific_client');
            const specificClientFields = document.getElementById('specific-client-fields');
            
            // Siempre mostrar la sección de cliente
            clientFields.style.display = 'block';
            
            if (documentType === '01') { // Factura
                // Para facturas, ocultar checkbox y mostrar campos directamente
                document.querySelector('.client-search-section').style.display = 'none';
                specificClientFields.style.display = 'block';
                useSpecificClient.checked = true;
                
                // Hacer campos requeridos
                document.getElementById('client_document_number').required = true;
                document.getElementById('client_business_name').required = true;
            } else {
                // Para boleta y nota de venta, mostrar checkbox
                document.querySelector('.client-search-section').style.display = 'block';
                useSpecificClient.checked = false;
                specificClientFields.style.display = 'none';
                
                // Quitar requerimiento
                document.getElementById('client_document_number').required = false;
                document.getElementById('client_business_name').required = false;
            }
        }
        
        function toggleSpecificClient() {
            const useSpecificClient = document.getElementById('use_specific_client');
            const specificClientFields = document.getElementById('specific-client-fields');
            const documentType = document.getElementById('document_type').value;
            
            if (useSpecificClient.checked) {
                specificClientFields.style.display = 'block';
                
                // Solo hacer requeridos si es factura
                if (documentType === '01') {
                    document.getElementById('client_document_number').required = true;
                    document.getElementById('client_business_name').required = true;
                }
            } else {
                specificClientFields.style.display = 'none';
                
                // Limpiar campos
                document.getElementById('client_document_number').value = '';
                document.getElementById('client_business_name').value = '';
                
                // Quitar requerimiento
                document.getElementById('client_document_number').required = false;
                document.getElementById('client_business_name').required = false;
            }
        }
        
        function searchExistingClient() {
            const documentNumber = document.getElementById('client_document_number').value;
            
            if (!documentNumber) {
                alert('Ingrese el número de documento para buscar');
                return;
            }
            
            // Llamar a Livewire para buscar el cliente
            @this.call('searchClient', documentNumber).then((client) => {
                if (client) {
                    document.getElementById('client_document_type').value = client.document_type;
                    document.getElementById('client_document_number').value = client.document_number;
                    document.getElementById('client_business_name').value = client.business_name;
                    
                    alert('Cliente encontrado: ' + client.business_name);
                } else {
                    alert('Cliente no encontrado. Puede ingresar los datos manualmente.');
                }
            }).catch((error) => {
                console.error('Error buscando cliente:', error);
                alert('Error al buscar cliente. Puede ingresar los datos manualmente.');
            });
        }
        
        function toggleCashFields() {
            const paymentMethod = document.getElementById('payment_method').value;
            const cashFields = document.getElementById('cash-fields');
            const cashCalculations = document.getElementById('cash-calculations');
            const digitalFields = document.getElementById('digital-payment-fields');
            const digitalInfo = document.getElementById('digital-payment-info');
            const digitalTitle = document.getElementById('digital-payment-title');
            const amountReceived = document.getElementById('amount_received');
            
            // Ocultar todos los campos primero
            cashFields.style.display = 'none';
            cashCalculations.style.display = 'none';
            digitalFields.style.display = 'none';
            digitalInfo.style.display = 'none';
            
            if (paymentMethod === 'cash') {
                cashFields.style.display = 'block';
                cashCalculations.style.display = 'block';
                amountReceived.required = true;
                calculateChange();
            } else if (paymentMethod === 'yape' || paymentMethod === 'plin') {
                digitalFields.style.display = 'block';
                digitalInfo.style.display = 'block';
                
                // Actualizar título según el método
                if (paymentMethod === 'yape') {
                    digitalTitle.textContent = '📱 Pago con Yape';
                } else {
                    digitalTitle.textContent = '📲 Pago con Plin';
                }
                
                // Actualizar total en la sección digital
                const totalAmountDigital = document.querySelector('.total-amount-digital');
                if (totalAmountDigital) {
                    totalAmountDigital.textContent = 'S/ ' + totalAmount.toFixed(2);
                }
                
                amountReceived.required = false;
                amountReceived.value = '';
            } else {
                // Otros métodos de pago (tarjeta, transferencia, etc.)
                amountReceived.required = false;
                amountReceived.value = '';
            }
            
            // Actualizar estado del botón de confirmar
            updateConfirmButton();
        }
        
        function generateQuickAmounts() {
            const container = document.getElementById('quick-amounts-container');
            if (!container) return;
            
            container.innerHTML = ''; // Limpiar contenido anterior
            
            if (totalAmount <= 0) {
                // Si no hay total, mostrar botones genéricos
                const defaultAmounts = [10, 20, 50, 100];
                defaultAmounts.forEach(amount => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'quick-amount-btn';
                    button.onclick = () => setQuickAmount(amount);
                    button.textContent = 'S/ ' + amount;
                    container.appendChild(button);
                });
                return;
            }
            
            // Calcular montos sugeridos
            const exactAmount = totalAmount;
            const roundTo10 = Math.ceil(totalAmount / 10) * 10;
            const roundTo20 = Math.ceil(totalAmount / 20) * 20;
            const roundTo50 = Math.ceil(totalAmount / 50) * 50;
            
            // Crear array de montos únicos y ordenados
            const amounts = [...new Set([exactAmount, roundTo10, roundTo20, roundTo50])]
                .filter(amount => amount > 0)
                .sort((a, b) => a - b);
            
            // Asegurar que tengamos al menos 4 botones
            while (amounts.length < 4) {
                const lastAmount = amounts[amounts.length - 1] || 50;
                const nextAmount = lastAmount + 50;
                if (!amounts.includes(nextAmount)) {
                    amounts.push(nextAmount);
                }
            }
            
            // Generar botones (máximo 4)
            amounts.slice(0, 4).forEach((amount, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'quick-amount-btn';
                button.onclick = () => setQuickAmount(amount);
                
                if (Math.abs(amount - exactAmount) < 0.01) {
                    button.textContent = 'Exacto';
                } else {
                    button.textContent = 'S/ ' + Math.round(amount);
                }
                
                container.appendChild(button);
            });
        }
        
        function calculateChange() {
            // Asegurar que tenemos el total más reciente
            totalAmount = getCurrentTotal();
            
            const paymentMethod = document.getElementById('payment_method').value;
            const amountReceivedInput = document.getElementById('amount_received');
            const amountReceived = parseFloat(amountReceivedInput.value) || 0;
            const change = amountReceived - totalAmount;
            
            console.log('Calculando vuelto - Total:', totalAmount, 'Recibido:', amountReceived, 'Vuelto:', change);
            
            // Actualizar total amount display
            const totalAmountElement = document.querySelector('.total-amount');
            if (totalAmountElement) {
                totalAmountElement.textContent = 'S/ ' + totalAmount.toFixed(2);
            }
            
            // Actualizar displays
            const receivedAmountElement = document.querySelector('.received-amount');
            if (receivedAmountElement) {
                receivedAmountElement.textContent = 'S/ ' + amountReceived.toFixed(2);
            }
            
            const changeElement = document.querySelector('.change-value');
            if (changeElement) {
                if (change >= 0) {
                    changeElement.textContent = 'S/ ' + change.toFixed(2);
                    changeElement.classList.remove('negative');
                } else {
                    changeElement.textContent = 'S/ ' + Math.abs(change).toFixed(2) + ' (Falta)';
                    changeElement.classList.add('negative');
                }
            }
            
            // Habilitar/deshabilitar botón de confirmar según el método de pago
            updateConfirmButton();
        }
        
        function updateConfirmButton() {
            const confirmBtn = document.querySelector('.payment-btn-confirm');
            const paymentMethod = document.getElementById('payment_method').value;
            
            if (!confirmBtn) return;
            
            if (paymentMethod === 'cash') {
                // Para efectivo, validar que el monto recibido sea suficiente
                const amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
                const change = amountReceived - totalAmount;
                
                if (change >= 0 && amountReceived > 0) {
                    confirmBtn.disabled = false;
                    confirmBtn.style.opacity = '1';
                } else {
                    confirmBtn.disabled = true;
                    confirmBtn.style.opacity = '0.6';
                }
            } else {
                // Para otros métodos de pago (yape, plin, tarjeta, etc.), siempre habilitar
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = '1';
            }
        }
        
        function setQuickAmount(amount) {
            document.getElementById('amount_received').value = amount.toFixed(2);
            calculateChange();
        }
        
        function confirmPayment() {
            const form = document.getElementById('payment-form');
            const formData = new FormData(form);
            const paymentMethod = document.getElementById('payment_method').value;
            const documentType = document.getElementById('document_type').value;
            const useSpecificClient = document.getElementById('use_specific_client').checked;
            
            // Validaciones especiales para efectivo
            if (paymentMethod === 'cash') {
                const amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;
                if (amountReceived < totalAmount) {
                    alert('El monto recibido debe ser mayor o igual al total a pagar.');
                    return;
                }
                
                // Agregar datos del vuelto
                const change = amountReceived - totalAmount;
                formData.append('amount_received', amountReceived);
                formData.append('change_amount', change);
            }
            
            // Agregar flag de cliente específico
            if (documentType !== '01' && useSpecificClient) {
                formData.append('use_specific_client', 'true');
            }
            
            // Agregar datos de pago digital si aplica
            if (paymentMethod === 'yape' || paymentMethod === 'plin') {
                const digitalReference = document.getElementById('digital_reference').value;
                const digitalPhone = document.getElementById('digital_phone').value;
                
                if (digitalReference) {
                    formData.append('digital_reference', digitalReference);
                }
                if (digitalPhone) {
                    formData.append('digital_phone', digitalPhone);
                }
            }
            
            // Validar campos requeridos
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Convertir FormData a objeto
            const paymentData = {};
            for (let [key, value] of formData.entries()) {
                paymentData[key] = value;
            }
            
            // Mostrar vuelto si es efectivo
            if (paymentMethod === 'cash' && paymentData.change_amount > 0) {
                const changeAmount = parseFloat(paymentData.change_amount);
                if (!confirm(`Vuelto a entregar: S/ ${changeAmount.toFixed(2)}\n\n¿Confirmar la venta?`)) {
                    return;
                }
            }
            
            // Guardar datos del carrito antes de procesar (ya que se limpia después)
            const currentCartItems = [];
            const currentTotal = getCurrentTotal();
            const currentSubtotal = getCurrentSubtotal();
            const currentIgv = getCurrentIgv();
            
            // Obtener productos del carrito antes de que se limpie
            try {
                const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                const cartItems = component.get('data.cart_items') || [];
                cartItems.forEach(item => {
                    currentCartItems.push({
                        description: item.description,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        total: item.total
                    });
                });
                console.log('Saved cart items before processing:', currentCartItems);
            } catch (e) {
                console.log('Error getting cart items, using fallback:', e);
                // Fallback: crear item genérico
                if (currentTotal > 0) {
                    currentCartItems.push({
                        description: 'Productos varios',
                        quantity: 1,
                        unit_price: currentTotal / 1.18,
                        total: currentTotal / 1.18
                    });
                }
            }
            
            // Llamar al método de Livewire
            @this.call('processSale', paymentData).then(() => {
                // Cerrar modal de pago
                closePaymentModal();
                
                // Esperar un momento y luego abrir modal de ticket
                setTimeout(() => {
                    // Llenar datos del ticket con la información guardada
                    populateTicketDataWithSavedData(formData, paymentMethod, documentType, currentCartItems, currentTotal, currentSubtotal, currentIgv);
                    
                    // Mostrar modal de ticket
                    document.getElementById('ticket-modal').style.display = 'flex';
                }, 500);
            }).catch((error) => {
                console.error('Error procesando pago:', error);
                alert('Error al procesar el pago. Por favor intente nuevamente.');
            });
        }
        
        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('payment-modal');
            if (event.target === modal) {
                closePaymentModal();
            }
        });
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const ticketModal = document.getElementById('ticket-modal');
                const paymentModal = document.getElementById('payment-modal');
                
                if (ticketModal.style.display !== 'none') {
                    closeTicketModal();
                } else if (paymentModal.style.display !== 'none') {
                    closePaymentModal();
                }
            }
        });
        
        // Funciones del modal de ticket
        
        function populateTicketDataWithSavedData(formData, paymentMethod, documentType, savedCartItems, savedTotal, savedSubtotal, savedIgv) {
            // Actualizar datos de la empresa en el ticket
            document.querySelector('.company-name').textContent = companyData.business_name;
            document.querySelector('.company-ruc').textContent = `RUC: ${companyData.ruc}`;
            document.querySelector('.company-address').textContent = `${companyData.address}, ${companyData.district} - ${companyData.province}`;
            
            // Actualizar teléfono y email si existen
            const phoneElement = document.querySelector('.company-phone');
            const emailElement = document.querySelector('.company-email');
            
            if (companyData.phone) {
                if (phoneElement) {
                    phoneElement.textContent = `Tel: ${companyData.phone}`;
                    phoneElement.style.display = 'block';
                } else {
                    const newPhoneElement = document.createElement('div');
                    newPhoneElement.className = 'company-phone';
                    newPhoneElement.textContent = `Tel: ${companyData.phone}`;
                    document.querySelector('.ticket-header').appendChild(newPhoneElement);
                }
            } else if (phoneElement) {
                phoneElement.style.display = 'none';
            }
            
            if (companyData.email) {
                if (emailElement) {
                    emailElement.textContent = `Email: ${companyData.email}`;
                    emailElement.style.display = 'block';
                } else {
                    const newEmailElement = document.createElement('div');
                    newEmailElement.className = 'company-email';
                    newEmailElement.textContent = `Email: ${companyData.email}`;
                    document.querySelector('.ticket-header').appendChild(newEmailElement);
                }
            } else if (emailElement) {
                emailElement.style.display = 'none';
            }
            
            // Actualizar tipo de documento
            const documentTypeNames = {
                '09': 'NOTA DE VENTA',
                '03': 'BOLETA DE VENTA', 
                '01': 'FACTURA'
            };
            document.getElementById('ticket-document-type').textContent = documentTypeNames[documentType] || 'DOCUMENTO';
            
            // Simular número de documento (en producción vendría del backend)
            const series = documentType === '09' ? 'NV01' : documentType === '03' ? 'B001' : 'F001';
            const number = String(Math.floor(Math.random() * 1000) + 1).padStart(8, '0');
            document.getElementById('ticket-document-number').textContent = `${series}-${number}`;
            
            // Fecha y hora actual
            const now = new Date();
            const dateStr = now.toLocaleDateString('es-PE') + ' - ' + now.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
            document.getElementById('ticket-document-date').textContent = dateStr;
            
            // Información del cliente
            const clientName = formData.get('client_business_name') || 'CLIENTE VARIOS';
            const clientDoc = formData.get('client_document_number') || '00000000';
            const clientDocType = formData.get('client_document_type') === '6' ? 'RUC' : 'DNI';
            
            document.querySelector('.client-name').textContent = `Cliente: ${clientName}`;
            document.querySelector('.client-document').textContent = `${clientDocType}: ${clientDoc}`;
            
            // Productos del carrito usando datos guardados
            populateTicketProductsWithSavedData(savedCartItems);
            
            // Totales usando datos guardados
            document.getElementById('ticket-subtotal').textContent = 'S/ ' + savedSubtotal.toFixed(2);
            document.getElementById('ticket-igv').textContent = 'S/ ' + savedIgv.toFixed(2);
            document.getElementById('ticket-total').textContent = 'S/ ' + savedTotal.toFixed(2);
            
            // Información de pago
            const paymentMethodNames = {
                'cash': 'EFECTIVO',
                'card': 'TARJETA',
                'yape': 'YAPE',
                'plin': 'PLIN',
                'transfer': 'TRANSFERENCIA',
                'other': 'OTRO'
            };
            document.getElementById('ticket-payment-method').textContent = `Método: ${paymentMethodNames[paymentMethod] || 'EFECTIVO'}`;
            
            // Datos específicos según método de pago
            if (paymentMethod === 'cash') {
                const amountReceived = parseFloat(formData.get('amount_received')) || savedTotal;
                const change = amountReceived - savedTotal;
                
                if (amountReceived > savedTotal) {
                    document.getElementById('ticket-payment-received').style.display = 'block';
                    document.getElementById('ticket-payment-received').textContent = `Recibido: S/ ${amountReceived.toFixed(2)}`;
                    document.getElementById('ticket-payment-change').style.display = 'block';
                    document.getElementById('ticket-payment-change').textContent = `Vuelto: S/ ${change.toFixed(2)}`;
                }
            } else if (paymentMethod === 'yape' || paymentMethod === 'plin') {
                const reference = formData.get('digital_reference');
                if (reference) {
                    document.getElementById('ticket-payment-reference').style.display = 'block';
                    document.getElementById('ticket-payment-reference').textContent = `Ref: ${reference}`;
                }
            }
        }
        
        function populateTicketData(formData, paymentMethod, documentType) {
            // Actualizar datos de la empresa en el ticket
            document.querySelector('.company-name').textContent = companyData.business_name;
            document.querySelector('.company-ruc').textContent = `RUC: ${companyData.ruc}`;
            document.querySelector('.company-address').textContent = `${companyData.address}, ${companyData.district} - ${companyData.province}`;
            
            // Actualizar teléfono y email si existen
            const phoneElement = document.querySelector('.company-phone');
            const emailElement = document.querySelector('.company-email');
            
            if (companyData.phone) {
                if (phoneElement) {
                    phoneElement.textContent = `Tel: ${companyData.phone}`;
                    phoneElement.style.display = 'block';
                } else {
                    // Crear elemento si no existe
                    const newPhoneElement = document.createElement('div');
                    newPhoneElement.className = 'company-phone';
                    newPhoneElement.textContent = `Tel: ${companyData.phone}`;
                    document.querySelector('.ticket-header').appendChild(newPhoneElement);
                }
            } else if (phoneElement) {
                phoneElement.style.display = 'none';
            }
            
            if (companyData.email) {
                if (emailElement) {
                    emailElement.textContent = `Email: ${companyData.email}`;
                    emailElement.style.display = 'block';
                } else {
                    // Crear elemento si no existe
                    const newEmailElement = document.createElement('div');
                    newEmailElement.className = 'company-email';
                    newEmailElement.textContent = `Email: ${companyData.email}`;
                    document.querySelector('.ticket-header').appendChild(newEmailElement);
                }
            } else if (emailElement) {
                emailElement.style.display = 'none';
            }
            
            // Actualizar tipo de documento
            const documentTypeNames = {
                '09': 'NOTA DE VENTA',
                '03': 'BOLETA DE VENTA', 
                '01': 'FACTURA'
            };
            document.getElementById('ticket-document-type').textContent = documentTypeNames[documentType] || 'DOCUMENTO';
            
            // Simular número de documento (en producción vendría del backend)
            const series = documentType === '09' ? 'NV01' : documentType === '03' ? 'B001' : 'F001';
            const number = String(Math.floor(Math.random() * 1000) + 1).padStart(8, '0');
            document.getElementById('ticket-document-number').textContent = `${series}-${number}`;
            
            // Fecha y hora actual
            const now = new Date();
            const dateStr = now.toLocaleDateString('es-PE') + ' - ' + now.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
            document.getElementById('ticket-document-date').textContent = dateStr;
            
            // Información del cliente
            const clientName = formData.get('client_business_name') || 'CLIENTE VARIOS';
            const clientDoc = formData.get('client_document_number') || '00000000';
            const clientDocType = formData.get('client_document_type') === '6' ? 'RUC' : 'DNI';
            
            document.querySelector('.client-name').textContent = `Cliente: ${clientName}`;
            document.querySelector('.client-document').textContent = `${clientDocType}: ${clientDoc}`;
            
            // Productos del carrito
            populateTicketProducts();
            
            // Totales
            const currentTotal = getCurrentTotal();
            const currentSubtotal = getCurrentSubtotal();
            const currentIgv = getCurrentIgv();
            
            console.log('Totals for ticket:', {currentTotal, currentSubtotal, currentIgv});
            
            document.getElementById('ticket-subtotal').textContent = 'S/ ' + currentSubtotal.toFixed(2);
            document.getElementById('ticket-igv').textContent = 'S/ ' + currentIgv.toFixed(2);
            document.getElementById('ticket-total').textContent = 'S/ ' + currentTotal.toFixed(2);
            
            // Información de pago
            const paymentMethodNames = {
                'cash': 'EFECTIVO',
                'card': 'TARJETA',
                'yape': 'YAPE',
                'plin': 'PLIN',
                'transfer': 'TRANSFERENCIA',
                'other': 'OTRO'
            };
            document.getElementById('ticket-payment-method').textContent = `Método: ${paymentMethodNames[paymentMethod] || 'EFECTIVO'}`;
            
            // Datos específicos según método de pago
            if (paymentMethod === 'cash') {
                const amountReceived = parseFloat(formData.get('amount_received')) || getCurrentTotal();
                const change = amountReceived - getCurrentTotal();
                
                if (amountReceived > getCurrentTotal()) {
                    document.getElementById('ticket-payment-received').style.display = 'block';
                    document.getElementById('ticket-payment-received').textContent = `Recibido: S/ ${amountReceived.toFixed(2)}`;
                    document.getElementById('ticket-payment-change').style.display = 'block';
                    document.getElementById('ticket-payment-change').textContent = `Vuelto: S/ ${change.toFixed(2)}`;
                }
            } else if (paymentMethod === 'yape' || paymentMethod === 'plin') {
                const reference = formData.get('digital_reference');
                if (reference) {
                    document.getElementById('ticket-payment-reference').style.display = 'block';
                    document.getElementById('ticket-payment-reference').textContent = `Ref: ${reference}`;
                }
            }
        }
        
        function populateTicketProductsWithSavedData(savedCartItems) {
            const productsList = document.getElementById('ticket-products-list');
            productsList.innerHTML = '';
            
            console.log('Populating ticket with saved cart items:', savedCartItems);
            
            if (!Array.isArray(savedCartItems) || savedCartItems.length === 0) {
                // Mostrar mensaje si no hay productos
                const noProductsDiv = document.createElement('div');
                noProductsDiv.className = 'product-item';
                noProductsDiv.innerHTML = `
                    <span class="product-desc">Sin productos</span>
                    <span class="product-qty">-</span>
                    <span class="product-price">-</span>
                    <span class="product-total">-</span>
                `;
                productsList.appendChild(noProductsDiv);
                return;
            }
            
            savedCartItems.forEach((item, index) => {
                try {
                    const productDiv = document.createElement('div');
                    productDiv.className = 'product-item';
                    
                    // Truncar descripción si es muy larga
                    let description = String(item.description || 'Producto');
                    if (description.length > 25) {
                        description = description.substring(0, 25) + '...';
                    }
                    
                    // Asegurar que los valores numéricos sean válidos
                    const quantity = parseInt(item.quantity) || 0;
                    const unitPrice = parseFloat(item.unit_price) || 0;
                    const total = parseFloat(item.total) || 0;
                    
                    productDiv.innerHTML = `
                        <span class="product-desc">${description}</span>
                        <span class="product-qty">${quantity}</span>
                        <span class="product-price">${unitPrice.toFixed(2)}</span>
                        <span class="product-total">${total.toFixed(2)}</span>
                    `;
                    
                    productsList.appendChild(productDiv);
                } catch (itemError) {
                    console.error('Error processing saved item at index', index, ':', itemError, item);
                }
            });
            
            console.log('Ticket products populated successfully with', savedCartItems.length, 'saved items');
        }
        
        function populateTicketProducts() {
            const productsList = document.getElementById('ticket-products-list');
            productsList.innerHTML = '';
            
            // Inicializar como array vacío
            let cartItems = [];
            
            try {
                // Método 1: Desde Livewire component
                const wireElement = document.querySelector('[wire\\:id]');
                if (wireElement) {
                    const wireId = wireElement.getAttribute('wire:id');
                    console.log('Wire ID found:', wireId);
                    
                    if (window.Livewire && window.Livewire.find) {
                        const component = window.Livewire.find(wireId);
                        if (component) {
                            console.log('Livewire component found:', component);
                            
                            // Intentar diferentes rutas para obtener los datos
                            let livewireItems = component.get('data.cart_items') || 
                                              component.data?.cart_items || 
                                              component.cart_items || 
                                              null;
                            
                            // Asegurar que sea un array
                            if (Array.isArray(livewireItems)) {
                                cartItems = livewireItems;
                            } else if (livewireItems && typeof livewireItems === 'object') {
                                // Si es un objeto, convertir a array
                                cartItems = Object.values(livewireItems);
                            }
                            
                            console.log('Cart items from Livewire:', cartItems);
                            console.log('Component data:', component.data);
                            console.log('All component properties:', Object.keys(component));
                        }
                    }
                }
                
                // Método 2: Desde elementos DOM del carrito visible
                if (!Array.isArray(cartItems) || cartItems.length === 0) {
                    console.log('Trying to get cart items from DOM...');
                    cartItems = []; // Reinicializar como array
                    
                    const cartElements = document.querySelectorAll('.pos-sidebar .product-item');
                    console.log('Found cart elements in DOM:', cartElements.length);
                    
                    cartElements.forEach((element, index) => {
                        const nameElement = element.querySelector('.product-name');
                        const qtyElement = element.querySelector('.qty-value');
                        const priceElement = element.querySelector('.price');
                        const totalElement = element.querySelector('.subtotal');
                        
                        if (nameElement && qtyElement && priceElement && totalElement) {
                            const name = nameElement.textContent.trim();
                            const qty = parseInt(qtyElement.textContent.trim()) || 0;
                            const price = parseFloat(priceElement.textContent.replace('S/', '').replace(',', '').trim()) || 0;
                            const total = parseFloat(totalElement.textContent.replace('S/', '').replace(',', '').trim()) || 0;
                            
                            if (name && qty > 0) {
                                cartItems.push({
                                    description: name,
                                    quantity: qty,
                                    unit_price: price,
                                    total: total
                                });
                            }
                        }
                    });
                    
                    console.log('Cart items from DOM:', cartItems);
                }
                
                // Asegurar que cartItems sea un array válido
                if (!Array.isArray(cartItems)) {
                    console.log('cartItems is not an array, converting:', typeof cartItems, cartItems);
                    cartItems = [];
                }
                
                // Si aún no hay productos pero hay total > 0, crear productos genéricos
                if (cartItems.length === 0 && totalAmount > 0) {
                    console.log('Creating generic products based on total:', totalAmount);
                    cartItems = [{
                        description: 'Productos varios',
                        quantity: 1,
                        unit_price: totalAmount / 1.18, // Sin IGV
                        total: totalAmount / 1.18
                    }];
                }
                
                console.log('Final cart items for ticket:', cartItems, 'Type:', typeof cartItems, 'Is Array:', Array.isArray(cartItems));
                
                if (!Array.isArray(cartItems) || cartItems.length === 0) {
                    // Mostrar mensaje si no hay productos
                    const noProductsDiv = document.createElement('div');
                    noProductsDiv.className = 'product-item';
                    noProductsDiv.innerHTML = `
                        <span class="product-desc">Sin productos</span>
                        <span class="product-qty">-</span>
                        <span class="product-price">-</span>
                        <span class="product-total">-</span>
                    `;
                    productsList.appendChild(noProductsDiv);
                    return;
                }
                
                // Validar que cartItems sea un array antes del forEach
                if (Array.isArray(cartItems) && cartItems.length > 0) {
                    cartItems.forEach((item, index) => {
                        try {
                            const productDiv = document.createElement('div');
                            productDiv.className = 'product-item';
                            
                            // Validar que item sea un objeto válido
                            if (!item || typeof item !== 'object') {
                                console.log('Invalid item at index', index, ':', item);
                                return;
                            }
                            
                            // Truncar descripción si es muy larga para ticket 80mm
                            let description = String(item.description || item.name || 'Producto');
                            if (description.length > 18) {
                                description = description.substring(0, 18) + '...';
                            }
                            
                            // Asegurar que los valores numéricos sean válidos
                            const quantity = parseInt(item.quantity) || 0;
                            const unitPrice = parseFloat(item.unit_price) || 0;
                            const total = parseFloat(item.total) || 0;
                            
                            productDiv.innerHTML = `
                                <span class="product-desc">${description}</span>
                                <span class="product-qty">${quantity}</span>
                                <span class="product-price">${unitPrice.toFixed(2)}</span>
                                <span class="product-total">${total.toFixed(2)}</span>
                            `;
                            
                            productsList.appendChild(productDiv);
                        } catch (itemError) {
                            console.error('Error processing item at index', index, ':', itemError, item);
                        }
                    });
                } else {
                    console.log('cartItems is not a valid array for forEach:', cartItems);
                }
                
                console.log('Products populated successfully:', cartItems.length, 'items');
            } catch (e) {
                console.error('Error obteniendo productos del carrito:', e);
                
                // Mostrar error en el ticket
                const errorDiv = document.createElement('div');
                errorDiv.className = 'product-item';
                errorDiv.innerHTML = `
                    <span class="product-desc">Error: ${e.message}</span>
                    <span class="product-qty">-</span>
                    <span class="product-price">-</span>
                    <span class="product-total">-</span>
                `;
                productsList.appendChild(errorDiv);
            }
        }
        
        function getCurrentSubtotal() {
            // Primero intentar desde el DOM
            const subtotalElement = document.querySelector('.total-row .total-value');
            if (subtotalElement) {
                const subtotalText = subtotalElement.textContent.replace('S/', '').replace(',', '').trim();
                const subtotalFromDOM = parseFloat(subtotalText);
                if (subtotalFromDOM > 0) {
                    console.log('Subtotal from DOM:', subtotalFromDOM);
                    return subtotalFromDOM;
                }
            }
            
            // Luego intentar desde Livewire
            try {
                const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                const subtotal = component.get('subtotal') || component.subtotal || 0;
                console.log('Subtotal from component:', subtotal);
                if (subtotal > 0) {
                    return parseFloat(subtotal);
                }
            } catch (e) {
                console.log('Error getting subtotal from Livewire:', e);
            }
            
            // Fallback: calcular desde el total
            const total = getCurrentTotal();
            const calculatedSubtotal = total > 0 ? total / 1.18 : 0;
            console.log('Calculated subtotal from total:', calculatedSubtotal);
            return calculatedSubtotal;
        }
        
        function getCurrentIgv() {
            // Primero intentar desde Livewire
            try {
                const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                const igv = component.get('igv') || component.igv || 0;
                console.log('IGV from component:', igv);
                if (igv > 0) {
                    return parseFloat(igv);
                }
            } catch (e) {
                console.log('Error getting IGV from Livewire:', e);
            }
            
            // Fallback: calcular desde el total
            const total = getCurrentTotal();
            const subtotal = total / 1.18;
            const calculatedIgv = total - subtotal;
            console.log('Calculated IGV from total:', calculatedIgv);
            return calculatedIgv;
        }
        
        function closeTicketModal() {
            document.getElementById('ticket-modal').style.display = 'none';
        }
        
        function printTicket() {
            window.print();
        }
        
        // Cerrar modal de ticket al hacer clic fuera
        document.addEventListener('click', function(event) {
            const ticketModal = document.getElementById('ticket-modal');
            if (event.target === ticketModal) {
                closeTicketModal();
            }
        });
    </script>
</x-filament-panels::page>