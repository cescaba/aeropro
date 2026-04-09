<?php
if (!defined('ABSPATH')) exit;

$grouped_nav = [];
foreach ($nav_items as $item) {
  $grouped_nav[$item['group']][] = $item;
}

$render_icon = static function (string $icon): string {
  switch ($icon) {
    case 'clipboard':
      return '<svg class="icon icon-aptest" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <g clip-path="url(#clip0_aptest)">
    <path d="M12.4999 1.66666H4.99992C4.55789 1.66666 4.13397 1.84225 3.82141 2.15481C3.50885 2.46737 3.33325 2.8913 3.33325 3.33332V16.6667C3.33325 17.1087 3.50885 17.5326 3.82141 17.8452C4.13397 18.1577 4.55789 18.3333 4.99992 18.3333H14.9999C15.4419 18.3333 15.8659 18.1577 16.1784 17.8452C16.491 17.5326 16.6666 17.1087 16.6666 16.6667V5.83332L12.4999 1.66666Z" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M11.6667 1.66666V4.99999C11.6667 5.44202 11.8423 5.86594 12.1549 6.1785C12.4675 6.49106 12.8914 6.66666 13.3334 6.66666H16.6667" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M7.5 12.5L9.16667 14.1667L12.5 10.8333" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <defs>
    <clipPath id="clip0_aptest">
      <rect width="20" height="20" fill="white"/>
    </clipPath>
  </defs>
</svg>';
    case 'user':
      return '<svg class="icon icon-profile-menu" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M15.8327 17.4993V15.8327C15.8327 14.9487 15.4815 14.1009 14.8564 13.4758C14.2313 12.8507 13.3835 12.4995 12.4995 12.4995H7.49971C6.61569 12.4995 5.76787 12.8507 5.14278 13.4758C4.51768 14.1009 4.1665 14.9487 4.1665 15.8327V17.4993" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M9.99971 9.16631C11.8406 9.16631 13.3329 7.67399 13.3329 5.83311C13.3329 3.99223 11.8406 2.49991 9.99971 2.49991C8.15883 2.49991 6.6665 3.99223 6.6665 5.83311C6.6665 7.67399 8.15883 9.16631 9.99971 9.16631Z" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';
    case 'lock':
      return '<svg class="icon icon-privacy-menu" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <g clip-path="url(#clip0_privacy)">
    <path d="M15.8333 9.16666H4.16667C3.24619 9.16666 2.5 9.91285 2.5 10.8333V16.6667C2.5 17.5871 3.24619 18.3333 4.16667 18.3333H15.8333C16.7538 18.3333 17.5 17.5871 17.5 16.6667V10.8333C17.5 9.91285 16.7538 9.16666 15.8333 9.16666Z" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.83325 9.16666V5.83332C5.83325 4.72825 6.27224 3.66845 7.05364 2.88704C7.83504 2.10564 8.89485 1.66666 9.99992 1.66666C11.105 1.66666 12.1648 2.10564 12.9462 2.88704C13.7276 3.66845 14.1666 4.72825 14.1666 5.83332V9.16666" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <defs>
    <clipPath id="clip0_privacy">
      <rect width="20" height="20" fill="white"/>
    </clipPath>
  </defs>
</svg>';
    case 'wallet':
      return '<svg class="icon icon-subscription" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M15.8333 5.83333V3.33333C15.8333 3.11232 15.7455 2.90036 15.5893 2.74408C15.433 2.5878 15.221 2.5 15 2.5H4.16667C3.72464 2.5 3.30072 2.67559 2.98816 2.98816C2.67559 3.30072 2.5 3.72464 2.5 4.16667C2.5 4.60869 2.67559 5.03262 2.98816 5.34518C3.30072 5.65774 3.72464 5.83333 4.16667 5.83333H16.6667C16.8877 5.83333 17.0996 5.92113 17.2559 6.07741C17.4122 6.23369 17.5 6.44565 17.5 6.66667V10M17.5 10H15C14.558 10 14.134 10.1756 13.8215 10.4882C13.5089 10.8007 13.3333 11.2246 13.3333 11.6667C13.3333 12.1087 13.5089 12.5326 13.8215 12.8452C14.134 13.1577 14.558 13.3333 15 13.3333H17.5C17.721 13.3333 17.933 13.2455 18.0893 13.0893C18.2455 12.933 18.3333 12.721 18.3333 12.5V10.8333C18.3333 10.6123 18.2455 10.4004 18.0893 10.2441C17.933 10.0878 17.721 10 17.5 10Z" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M2.5 4.16669V15.8334C2.5 16.2754 2.67559 16.6993 2.98816 17.0119C3.30072 17.3244 3.72464 17.5 4.16667 17.5H16.6667C16.8877 17.5 17.0996 17.4122 17.2559 17.2559C17.4122 17.0997 17.5 16.8877 17.5 16.6667V13.3334" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';
    case 'help':
      return '<svg class="icon icon-help" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <g clip-path="url(#clip0_help)">
    <path d="M9.99951 18.3326C14.6017 18.3326 18.3325 14.6018 18.3325 9.99963C18.3325 5.39744 14.6017 1.66663 9.99951 1.66663C5.39732 1.66663 1.6665 5.39744 1.6665 9.99963C1.6665 14.6018 5.39732 18.3326 9.99951 18.3326Z" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M7.57471 7.49968C7.77062 6.94275 8.15731 6.47314 8.66629 6.17401C9.17527 5.87487 9.7737 5.76553 10.3556 5.86533C10.9375 5.96514 11.4652 6.26766 11.8454 6.71931C12.2256 7.17097 12.4337 7.7426 12.4328 8.33298C12.4328 9.99958 9.93295 10.8329 9.93295 10.8329" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M9.99951 14.1661H10.0078" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <defs>
    <clipPath id="clip0_help">
      <rect width="19.9992" height="19.9992" fill="white"/>
    </clipPath>
  </defs>
</svg>';
    case 'cards':
    default:
      return '<svg class="icon icon-flashcards" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <g clip-path="url(#clip0_flashcards)">
    <path d="M9.99951 14.9994V4.16649" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M12.4996 10.8329C11.7786 10.6222 11.1453 10.1835 10.6946 9.58254C10.2439 8.98163 10.0001 8.25085 9.99966 7.49971C9.99921 8.25085 9.75537 8.98163 9.30469 9.58254C8.854 10.1835 8.22073 10.6222 7.49976 10.8329" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M14.6644 5.41645C14.8561 5.08437 14.9689 4.71263 14.994 4.32999C15.0191 3.94735 14.9558 3.56406 14.8091 3.20978C14.6624 2.8555 14.4361 2.53973 14.1478 2.28689C13.8595 2.03405 13.5169 1.85092 13.1465 1.75167C12.7761 1.65242 12.3878 1.63971 12.0117 1.71453C11.6356 1.78934 11.2818 1.94967 10.9776 2.18312C10.6734 2.41656 10.4269 2.71686 10.2573 3.06078C10.0877 3.40471 9.99954 3.78304 9.99956 4.1665C9.99958 3.78304 9.91138 3.40471 9.74179 3.06078C9.5722 2.71686 9.32576 2.41656 9.02154 2.18312C8.71732 1.94967 8.36348 1.78934 7.98738 1.71453C7.61129 1.63971 7.22302 1.65242 6.85263 1.75167C6.48223 1.85092 6.13963 2.03405 5.85133 2.28689C5.56303 2.53973 5.33676 2.8555 5.19002 3.20978C5.04329 3.56406 4.98002 3.94735 5.00512 4.32999C5.03021 4.71263 5.14299 5.08437 5.33474 5.41645" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M14.9968 4.27068C15.4866 4.39662 15.9414 4.63237 16.3266 4.96008C16.7118 5.28778 17.0174 5.69885 17.2202 6.16214C17.423 6.62544 17.5178 7.12881 17.4972 7.63414C17.4767 8.13947 17.3415 8.6335 17.1017 9.07882" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M14.9995 14.9994C15.7332 14.9994 16.4465 14.7572 17.0285 14.3106C17.6106 13.8639 18.0291 13.2376 18.219 12.5289C18.4089 11.8202 18.3596 11.0686 18.0789 10.3907C17.7981 9.71284 17.3015 9.14654 16.6661 8.77965" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M16.6386 14.5686C16.697 15.0204 16.6621 15.4795 16.5362 15.9173C16.4102 16.3552 16.1958 16.7625 15.9063 17.1143C15.6167 17.466 15.2581 17.7547 14.8526 17.9624C14.4471 18.1702 14.0033 18.2925 13.5487 18.3221C13.0941 18.3516 12.6382 18.2875 12.2092 18.134C11.7803 17.9804 11.3874 17.7405 11.0548 17.4291C10.7222 17.1177 10.457 16.7415 10.2755 16.3236C10.094 15.9057 10.0001 15.455 9.99965 14.9994C9.99917 15.455 9.90528 15.9057 9.7238 16.3236C9.54232 16.7415 9.27709 17.1177 8.9445 17.4291C8.6119 17.7405 8.219 17.9804 7.79006 18.134C7.36113 18.2875 6.90526 18.3516 6.45061 18.3221C5.99596 18.2925 5.55219 18.1702 5.14669 17.9624C4.7412 17.7547 4.3826 17.466 4.09304 17.1143C3.80348 16.7625 3.5891 16.3552 3.46315 15.9173C3.3372 15.4795 3.30235 15.0204 3.36075 14.5686" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M4.9998 14.9994C4.26608 14.9994 3.55287 14.7572 2.97077 14.3106C2.38868 13.8639 1.97023 13.2376 1.78033 12.5289C1.59042 11.8202 1.63967 11.0686 1.92044 10.3907C2.20121 9.71284 2.6978 9.14654 3.3332 8.77965" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.00218 4.27068C4.51237 4.39662 4.05764 4.63237 3.67242 4.96008C3.28721 5.28778 2.98163 5.69885 2.7788 6.16214C2.57598 6.62544 2.48125 7.12881 2.50177 7.63414C2.52229 8.13947 2.65754 8.6335 2.89726 9.07882" stroke="currentColor" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <defs>
    <clipPath id="clip0_flashcards">
      <rect width="19.9992" height="19.9992" fill="white"/>
    </clipPath>
  </defs>
</svg>';
  }
};
?>
<div class="vc-dashboard-shell">
  <aside class="vc-dashboard-sidebar">
    <div class="vc-dashboard-brand">
      <img src="<?php echo esc_url($logo_url); ?>" alt="Aeropro">
    </div>

    <nav class="vc-dashboard-nav" aria-label="Dashboard navigation">
      <?php foreach ($grouped_nav as $group_label => $items): ?>
        <div class="vc-dashboard-nav-group">
          <p class="vc-dashboard-nav-title"><?php echo esc_html($group_label); ?></p>
          <?php foreach ($items as $item): ?>
            <a
              class="vc-dashboard-nav-link<?php echo !empty($item['is_active']) ? ' is-active' : ''; ?>"
              href="<?php echo esc_url($item['url']); ?>"
              <?php echo !empty($item['is_active']) ? 'aria-current="page"' : ''; ?>
            >
              <span class="vc-dashboard-nav-icon"><?php echo $render_icon($item['icon']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
              <span><?php echo esc_html($item['label']); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>

    <div class="vc-dashboard-sidebar-footer">
      <a class="vc-dashboard-logout" href="<?php echo esc_url($logout_url); ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 17l5-5-5-5M20 12H9M11 20H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Log out</span>
      </a>
    </div>
  </aside>

  <div class="vc-dashboard-main">
    <header class="vc-dashboard-topbar">
      <label class="vc-dashboard-search" aria-label="Search">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17.5 17.5L13.8833 13.8833" stroke="#666666" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.16667 15.8333C12.8486 15.8333 15.8333 12.8486 15.8333 9.16667C15.8333 5.48477 12.8486 2.5 9.16667 2.5C5.48477 2.5 2.5 5.48477 2.5 9.16667C2.5 12.8486 5.48477 15.8333 9.16667 15.8333Z" stroke="#666666" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
        <input type="search" placeholder="<?php echo esc_attr($search_placeholder); ?>" aria-label="<?php echo esc_attr($search_placeholder); ?>">
      </label>

      <div class="vc-dashboard-topbar-actions">
        <!-- <button class="vc-dashboard-bell" type="button" aria-label="Notifications">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8m6 12a3 3 0 0 0 2.12-.88A3 3 0 0 0 15 18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span class="vc-dashboard-bell-dot"></span>
        </button> -->

        <div class="vc-dashboard-user-pill">
          <span class="vc-dashboard-avatar" data-vc-dashboard-avatar>
            <?php if (!empty($avatar_url)): ?>
              <img class="vc-dashboard-avatar-image" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>">
            <?php else: ?>
              <span class="vc-dashboard-avatar-fallback"><?php echo esc_html($initials); ?></span>
            <?php endif; ?>
          </span>
          <div class="vc-dashboard-user-copy">
            <strong><?php echo esc_html($display_name); ?></strong>
            <small><?php echo esc_html($membership_label); ?></small>
          </div>
        </div>
      </div>
    </header>

    <main class="vc-dashboard-content">
      <header class="vc-dashboard-heading">
        <h1 id="vc-dashboard-panel-title" class="header-h1"><?php echo esc_html($page_title); ?></h1>
        <p><?php echo esc_html($page_subtitle); ?></p>
        <div class="vc-dashboard-heading-session-meta" hidden></div>
      </header>

      <section class="vc-dashboard-panel vc-dashboard-panel--<?php echo esc_attr($active_view); ?>" aria-labelledby="vc-dashboard-panel-title">
        <?php echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </section>
    </main>
  </div>
</div>
