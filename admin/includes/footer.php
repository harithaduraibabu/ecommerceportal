    </div><!-- /.site-container -->
</main><!-- /.main-content -->

<footer class="site-footer" style="background-color: #0b1120; border-top: 1px solid #1e293b; padding: 20px 0; margin-top: auto;">
    <div class="site-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 13px;">
        <div>
            <strong>Smart E-Commerce Portal</strong> &bull; Admin Management Suite
        </div>
        <div>
            Logged in as <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong> &bull; <a href="logout.php" style="color: #f87171;">Logout</a>
        </div>
    </div>
</footer>

<!-- Basic JavaScript File for Action Confirmation Popups (confirmProductDelete) -->
<script src="../assets/js/script.js"></script>

</body>
</html>

