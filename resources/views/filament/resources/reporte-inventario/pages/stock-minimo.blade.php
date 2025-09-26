<x-filament-panels::page>
    <style>
        .kardex-container {
            display: flex;
            gap: 2rem;
            min-height: calc(100vh - 200px);
        }
        
        .kardex-sidebar {
            width: 320px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #92400e 100%);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15), 0 8px 16px rgba(0,0,0,0.1);
            position: sticky;
            top: 1rem;
            height: fit-content;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        
        .kardex-main {
            flex: 1;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .sidebar-nav {
            margin-bottom: 2.5rem;
        }
        
        .sidebar-nav h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sidebar-nav h3::before {
            content: "📊";
            font-size: 1.5rem;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            border-radius: 12px;
            text-decoration: none;
            color: rgba(255,255,255,0.85);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,0.15);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .nav-item:hover::before {
            left: 100%;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(6px) scale(1.02);
            border-color: rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .nav-item.active {
            background: rgba(255,255,255,0.25);
            color: white;
            border-color: rgba(255,255,255,0.4);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            font-weight: 600;
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(255,255,255,0.6);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
            border-radius: 16px;
            padding: 1.75rem;
            color: white;
            text-align: center;
            box-shadow: 0 8px 24px rgba(251, 191, 36, 0.25);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 32px rgba(251, 191, 36, 0.35);
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);
        }
        
        .stat-card.success:hover {
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.35);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);
        }
        
        .stat-card.warning:hover {
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.35);
        }
        
        .stat-card.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.25);
        }
        
        .stat-card.danger:hover {
            box-shadow: 0 12px 32px rgba(239, 68, 68, 0.35);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.95;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        /* Filament table enhancements */
        .table-container :deep(.fi-ta-table) {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table-container :deep(.fi-ta-header-cell) {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            font-weight: 600;
            border-bottom: 2px solid #f59e0b;
        }
        
        .table-container :deep(.fi-ta-row:hover) {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }
        
        @media (max-width: 768px) {
            .kardex-container {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .kardex-sidebar {
                width: 100%;
                position: static;
                padding: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="kardex-container">
        <!-- Sidebar Navigation -->
        <div class="kardex-sidebar">
            <div class="sidebar-nav">
                <h3 class="text-lg font-semibold mb-6">
                    Reportes
                </h3>
                
                <nav class="space-y-2">
                    <a href="{{ route('filament.admin.resources.reporte-inventario.kardex-sencillo') }}" 
                       class="nav-item">
                        Kardex Sencillo
                    </a>
                    <a href="{{ route('filament.admin.resources.reporte-inventario.stock-actual') }}" 
                       class="nav-item">
                        Stock Actual
                    </a>
                    <a href="{{ route('filament.admin.resources.reporte-inventario.stock-minimo') }}" 
                       class="nav-item active">
                        Stock Mínimo
                    </a>
                </nav>
            </div>
            
            <!-- Statistics Section -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-number">{{ $stockDisponible ?? 0 }}</div>
                    <div class="stat-label">Stock Disponible</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-number">{{ $stockAgotado ?? 0 }}</div>
                    <div class="stat-label">Stock Agotado</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-number">{{ $stockCritico ?? 0 }}</div>
                    <div class="stat-label">Stock Crítico</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">{{ $stockMinimo ?? 0 }}</div>
                    <div class="stat-label">Stock Mínimo</div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="kardex-main">
            <div class="table-container">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>