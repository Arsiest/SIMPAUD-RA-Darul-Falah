<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPAUD RA Darul Falah</title>
    <!-- Google Fonts: Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* Custom Color Palette */
        :root {
            --feather-beige: #F2EFE9;
            --dusty-olive: #8B8C7A;
            --herb-leaf: #7A916E;
            --night-green: #1A2F22;
        }

        body { font-family: 'Nunito', sans-serif; background-color: var(--feather-beige); color: var(--night-green); }
        
        /* 1. Night Green (Primary Action, Headings, High Contrast) */
        .text-primary, .text-navy, .text-dark { color: var(--night-green) !important; }
        .bg-primary, .bg-navy, .bg-dark, .hero-header { background-color: var(--night-green) !important; color: #FFFFFF !important; }
        .btn-primary { background-color: var(--night-green) !important; border-color: var(--night-green) !important; color: #FFFFFF !important; }
        .btn-primary:hover { background-color: #122117 !important; border-color: #122117 !important; }
        .btn-outline-primary { color: var(--night-green) !important; border-color: var(--night-green) !important; }
        .btn-outline-primary:hover { background-color: var(--night-green) !important; color: #FFFFFF !important; }
        .bg-primary.bg-opacity-10 { background-color: rgba(26, 47, 34, 0.1) !important; color: var(--night-green) !important; }

        /* 2. Herb Leaf (Success, Active, Positive, Progress Bar) */
        .text-success { color: var(--herb-leaf) !important; }
        .bg-success { background-color: var(--herb-leaf) !important; color: #FFFFFF !important; }
        .btn-success { background-color: var(--herb-leaf) !important; border-color: var(--herb-leaf) !important; color: #FFFFFF !important; }
        .btn-success:hover { background-color: #637759 !important; border-color: #637759 !important; }
        .btn-outline-success { color: var(--herb-leaf) !important; border-color: var(--herb-leaf) !important; }
        .btn-outline-success:hover { background-color: var(--herb-leaf) !important; color: #FFFFFF !important; }
        .bg-success.bg-opacity-10 { background-color: rgba(122, 145, 110, 0.15) !important; color: var(--herb-leaf) !important; }
        .border-success { border-color: var(--herb-leaf) !important; }
        .progress-bar { background-color: var(--herb-leaf) !important; }
        
        /* 3. Dusty Olive (Secondary, Muted Text, Borders, Icons) */
        .text-muted, .text-secondary { color: var(--dusty-olive) !important; }
        .bg-secondary { background-color: var(--dusty-olive) !important; color: #FFFFFF !important; }
        .btn-secondary { background-color: var(--dusty-olive) !important; border-color: var(--dusty-olive) !important; color: #FFFFFF !important; }
        .btn-secondary:hover { background-color: #717363 !important; border-color: #717363 !important; }
        .btn-outline-secondary { color: var(--dusty-olive) !important; border-color: var(--dusty-olive) !important; }
        .btn-outline-secondary:hover { background-color: var(--dusty-olive) !important; color: #FFFFFF !important; }
        .border-secondary { border-color: var(--dusty-olive) !important; }
        .border, .border-bottom, .border-top, .border-end, .border-start { border-color: rgba(139, 140, 122, 0.25) !important; } /* Soft dusty olive border */

        /* 4. Feather Beige (Cards, Topbar) */
        .custom-card, .topbar { background-color: #FFFFFF !important; border: 1px solid rgba(139, 140, 122, 0.2) !important; }
        
        /* 5. Sidebar Styling (Night Green) */
        #sidebar { min-width: 250px; max-width: 250px; min-height: 100vh; background-color: var(--night-green) !important; color: var(--feather-beige) !important; border-right: none; }
        #sidebar .text-navy, #sidebar .text-dark { color: var(--feather-beige) !important; }
        .sidebar-heading { font-size: 0.75rem; font-weight: 800; color: rgba(242, 239, 233, 0.5); letter-spacing: 0.5px; margin-top: 1.5rem; margin-bottom: 0.5rem; padding-left: 1rem; }
        .nav-link { color: rgba(242, 239, 233, 0.75); font-weight: 600; padding: 0.6rem 1rem; border-radius: 8px; margin: 0.2rem 0.5rem; transition: all 0.2s; }
        .nav-link:hover { background-color: rgba(242, 239, 233, 0.1); color: var(--feather-beige) !important; }
        .nav-link.active { background-color: var(--herb-leaf) !important; color: #FFFFFF !important; font-weight: 700; }
        .nav-link i { width: 24px; text-align: center; margin-right: 8px; }

        /* Topbar & Content */
        .topbar { padding: 0.75rem 1.5rem; }
        .main-content { flex-grow: 1; padding: 1.5rem 1.5rem 4rem 1.5rem; height: 100vh; overflow-y: auto; }
        
        /* Cards & Utilities */
        .hero-header { border-radius: 8px; padding: 2rem; margin-bottom: 1.5rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.05); }
        .custom-card { border-radius: 8px; box-shadow: 0 .125rem .25rem rgba(0,0,0,.05); }
        
        /* Custom Input Material Style */
        .form-floating-custom { border: 1px solid rgba(139, 140, 122, 0.3); border-radius: 4px; padding: 10px 15px; position: relative; margin-bottom: 15px; background: #fff; }
        .form-floating-custom label { position: absolute; top: -10px; left: 10px; background: #fff; padding: 0 5px; font-size: 11px; color: var(--dusty-olive); font-weight: 700; text-transform: uppercase; }
        .form-floating-custom input, .form-floating-custom select { border: none; width: 100%; outline: none; background: transparent; font-size: 14px; color: var(--night-green); }
    </style>
</head>
<body class="d-flex overflow-hidden">
