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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .kardex-container {
                flex-direction: column;
            }
            
            .kardex-sidebar {
                width: 100%;
                position: relative;
            }
        }
    </style>

    <!-- Main Content -->
    <div class="kardex-container">
        <!-- Sidebar Navigation -->
        <div class="kardex-sidebar">
            <nav class="sidebar-nav">
                <h3>Reportes de Inventario</h3>
                <a href="{{ route('filament.admin.resources.reporte-inventario.stock-actual') }}" class="nav-item active">
                    Stock Actual
                </a>
                <a href="{{ route('filament.admin.resources.reporte-inventario.stock-minimo') }}" class="nav-item">
                    Stock Mínimo
                </a>
                <a href="{{ route('filament.admin.resources.reporte-inventario.kardex-sencillo') }}" class="nav-item">
                    Kardex Sencillo
                </a>
            </nav>
        </div>
        
        <!-- Main Content Area -->
        <div class="kardex-main">
            <!-- Content will be loaded here -->
        </div>
    </div>
</x-filament-panels::page>