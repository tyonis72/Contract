<!-- NAVBAR -->
<nav class="navbar navbar-light bg-white shadow-sm px-3">
    <button class="btn btn-outline-dark me-2" id="menu-toggle">
        <i class="fa fa-bars"></i>
    </button>
    <span class="fw-semibold fs-5 text-secondary me-auto">
        <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?>
        <?php if (isset($page_subtitle)): ?>
            <span class="text-muted fs-6"><?php echo htmlspecialchars($page_subtitle); ?></span>
        <?php endif; ?>
    </span>
    <div class="d-flex align-items-center">
        <i class="fa fa-user-circle me-2 fs-4"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
    </div>
</nav>