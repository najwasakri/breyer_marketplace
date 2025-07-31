</php
class="sidebar"></div>
    <ul class="menu-items">
        <li><a href="dashboard.php">HALAMAN UTAMA</a></li>
        <li><a href="manage_inventory.php">INVENTORI</a></li>
        <li><a href="manage_payments.php">PEMBAYARAN</a></li>
        <li><a href="manage_receipts.php">RESIT</a></li>
        <li><a href="support_tickets.php">HELP & SUPPORT</a></li>
    </ul>
</div>

<style>
    .sidebar {
        width: 250px;
        background: #f0f0f0;
        padding: 20px 0;
    }

    .menu-title {
        padding: 15px;
        font-size: 1.2rem;
        font-weight: bold;
        background: #FFE45C;
    }

    .menu-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-items li {
        padding: 12px 20px;
    }

    .menu-items li a {
        text-decoration: none;
        color: #333;
    }

    .menu-items li.active {
        background: #FFE45C;
    }
</style>