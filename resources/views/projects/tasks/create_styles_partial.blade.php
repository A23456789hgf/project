<style>
    /* Extracted styles from create.blade.php for reuse in edit view */
    :root {
        --card-bg: #ffffff;
        --input-bg: #f8fafc;
        --input-border: #e2e8f0;
        --input-focus-border: #3b82f6;
        --label-color: #334155;
        --section-bg: #f1f5f9;
        --switch-active: #3b82f6;
        --danger: #ef4444;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04);
        --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        --shadow-lg: 0 20px 50px -12px rgba(0, 0, 0, 0.08);
        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
    }
    .create-task-card { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); overflow: hidden; transition: box-shadow 0.3s ease; }
    .create-task-card:hover { box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.1); }
    .card-header-custom { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 1.5rem 2rem; position: relative; overflow: hidden; }
    .card-header-custom h5 { color: #fff; font-weight: 700; margin: 0; font-size: 1.1rem; position: relative; z-index: 1; }
    .form-control, .form-select { padding: 0.7rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--input-border); background-color: var(--input-bg); transition: all 0.25s ease; font-size: 0.9rem; color: #1e293b; }
    .form-control:focus, .form-select:focus { background-color: #ffffff; border-color: var(--input-focus-border); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08); outline: none; }
    .activities-section { background: linear-gradient(135deg, #f0f9ff 0%, #eff6ff 100%); border: 1.5px dashed #93c5fd; border-radius: var(--radius-md); padding: 1.5rem; }
    .toggle-card { background: var(--section-bg); border-radius: var(--radius-md); padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
    .btn-premium { border-radius: var(--radius-sm); padding: 0.7rem 1.75rem; font-weight: 600; }
    .btn-save { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none; color: #fff; }
    .card-footer-custom { background: #fafbfc; border-top: 1px solid #f1f5f9; padding: 1.25rem 2rem; }
</style>
