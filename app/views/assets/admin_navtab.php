<ul class="nav nav-tabs mt-4">
    <li class="nav-item">
        <a class="nav-link <?= basename(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)) == 'booking-requests' ? 'active' : ''; ?>" aria-current="page" href="/admin/booking-requests">Bookings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  <?= basename(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)) == 'rebooking-requests' ? 'active' : ''; ?>" href="/admin/rebooking-requests">Rebooking Requests</a>
    </li>
</ul>