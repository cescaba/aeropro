<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-subscription-shell">
  <article class="vc-subscription-summary">
    <div class="vc-subscription-summary-top">
      <div class="vc-subscription-plan">
<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M0 10C0 4.47715 4.47715 0 10 0H30C35.5228 0 40 4.47715 40 10V30C40 35.5228 35.5228 40 30 40H10C4.47715 40 0 35.5228 0 30V10Z" fill="#F1F5F9"/>
<path d="M19.6352 12.7217C19.6712 12.6563 19.724 12.6018 19.7882 12.5639C19.8524 12.526 19.9256 12.506 20.0002 12.506C20.0748 12.506 20.148 12.526 20.2122 12.5639C20.2764 12.6018 20.3292 12.6563 20.3652 12.7217L22.8252 17.3917C22.8839 17.4998 22.9657 17.5936 23.0649 17.6663C23.1642 17.7391 23.2783 17.7889 23.399 17.8124C23.5198 17.8358 23.6443 17.8322 23.7635 17.8018C23.8827 17.7714 23.9937 17.715 24.0885 17.6367L27.6527 14.5833C27.7211 14.5277 27.8054 14.4952 27.8935 14.4905C27.9816 14.4858 28.0688 14.5092 28.1428 14.5573C28.2167 14.6054 28.2735 14.6757 28.3049 14.7581C28.3363 14.8405 28.3408 14.9307 28.3177 15.0158L25.956 23.5542C25.9078 23.7289 25.804 23.8831 25.6602 23.9935C25.5164 24.1039 25.3406 24.1644 25.1594 24.1658H14.8419C14.6605 24.1646 14.4844 24.1041 14.3405 23.9937C14.1966 23.8834 14.0926 23.729 14.0444 23.5542L11.6835 15.0167C11.6604 14.9315 11.6649 14.8413 11.6963 14.7589C11.7277 14.6765 11.7845 14.6062 11.8584 14.5581C11.9324 14.51 12.0197 14.4866 12.1077 14.4913C12.1958 14.496 12.2801 14.5285 12.3485 14.5842L15.9119 17.6375C16.0067 17.7159 16.1177 17.7723 16.2369 17.8026C16.3561 17.833 16.4806 17.8366 16.6014 17.8132C16.7221 17.7898 16.8362 17.7399 16.9354 17.6672C17.0346 17.5944 17.1165 17.5006 17.1752 17.3925L19.6352 12.7217Z" stroke="#325CBA" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M14.167 27.5H25.8337" stroke="#325CBA" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

        <div>
          <small>Current Plan</small>
          <strong><?php echo esc_html($plan_name); ?></strong>
        </div>
      </div>

      <div class="vc-subscription-summary-actions">
        <span class="vc-subscription-badge <?php echo esc_attr($status_class); ?>">
          <?php if ($status_class === 'is-active'): ?>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
              <path d="M7.99967 14.6667C11.6816 14.6667 14.6663 11.6819 14.6663 8.00001C14.6663 4.31811 11.6816 1.33334 7.99967 1.33334C4.31778 1.33334 1.33301 4.31811 1.33301 8.00001C1.33301 11.6819 4.31778 14.6667 7.99967 14.6667Z" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M6 7.99999L7.33333 9.33332L10 6.66666" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          <?php endif; ?>
          <span><?php echo esc_html($status_label); ?></span>
        </span>
        <a class="vc-subscription-manage" href="#vc-subscription-management">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.44754 2.75732C6.48427 2.37088 6.66376 2.01202 6.95094 1.75084C7.23812 1.48967 7.61235 1.34494 8.00054 1.34494C8.38872 1.34494 8.76296 1.48967 9.05014 1.75084C9.33732 2.01202 9.5168 2.37088 9.55354 2.75732C9.57561 3.00696 9.65751 3.2476 9.79229 3.45888C9.92707 3.67016 10.1108 3.84586 10.3278 3.97111C10.5449 4.09635 10.789 4.16746 11.0393 4.1784C11.2897 4.18935 11.539 4.13981 11.7662 4.03399C12.1189 3.87384 12.5187 3.85067 12.8875 3.96898C13.2564 4.08729 13.5681 4.33862 13.7619 4.67406C13.9557 5.00949 14.0177 5.40504 13.936 5.7837C13.8542 6.16237 13.6345 6.49707 13.3195 6.72266C13.1145 6.86655 12.9471 7.05773 12.8315 7.28001C12.7159 7.50228 12.6556 7.74913 12.6556 7.99966C12.6556 8.25018 12.7159 8.49703 12.8315 8.71931C12.9471 8.94158 13.1145 9.13276 13.3195 9.27666C13.6345 9.50224 13.8542 9.83694 13.936 10.2156C14.0177 10.5943 13.9557 10.9898 13.7619 11.3253C13.5681 11.6607 13.2564 11.912 12.8875 12.0303C12.5187 12.1486 12.1189 12.1255 11.7662 11.9653C11.539 11.8595 11.2897 11.81 11.0393 11.8209C10.789 11.8319 10.5449 11.903 10.3278 12.0282C10.1108 12.1534 9.92707 12.3291 9.79229 12.5404C9.65751 12.7517 9.57561 12.9924 9.55354 13.242C9.5168 13.6284 9.33732 13.9873 9.05014 14.2485C8.76296 14.5096 8.38872 14.6544 8.00054 14.6544C7.61235 14.6544 7.23812 14.5096 6.95094 14.2485C6.66376 13.9873 6.48427 13.6284 6.44754 13.242C6.4255 12.9923 6.3436 12.7515 6.20878 12.5402C6.07396 12.3288 5.89018 12.1531 5.67302 12.0278C5.45586 11.9025 5.21172 11.8315 4.96126 11.8206C4.7108 11.8097 4.4614 11.8594 4.2342 11.9653C3.88146 12.1255 3.48175 12.1486 3.11287 12.0303C2.74399 11.912 2.43232 11.6607 2.23853 11.3253C2.04473 10.9898 1.98268 10.5943 2.06444 10.2156C2.14621 9.83694 2.36594 9.50224 2.68087 9.27666C2.88595 9.13276 3.05336 8.94158 3.16893 8.71931C3.2845 8.49703 3.34484 8.25018 3.34484 7.99966C3.34484 7.74913 3.2845 7.50228 3.16893 7.28001C3.05336 7.05773 2.88595 6.86655 2.68087 6.72266C2.36638 6.49695 2.14704 6.16239 2.06547 5.78398C1.9839 5.40557 2.04594 5.01035 2.23953 4.67513C2.43311 4.33991 2.74441 4.08864 3.11293 3.97015C3.48145 3.85166 3.88086 3.87441 4.23354 4.03399C4.46071 4.13981 4.71003 4.18935 4.9604 4.1784C5.21078 4.16746 5.45482 4.09635 5.67189 3.97111C5.88896 3.84586 6.07266 3.67016 6.20745 3.45888C6.34223 3.2476 6.42413 3.00696 6.4462 2.75732" stroke="#325CBA" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8 10C9.10457 10 10 9.10457 10 8C10 6.89543 9.10457 6 8 6C6.89543 6 6 6.89543 6 8C6 9.10457 6.89543 10 8 10Z" stroke="#325CBA" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Manage</span>
        </a>
      </div>
    </div>

    <div class="vc-subscription-metrics">
      <div>
        <small>Price</small>
        <strong><?php echo esc_html($price_label); ?></strong>
      </div>
      <div>
        <small><?php echo esc_html($billing_date_label); ?></small>
        <strong><?php echo esc_html($next_payment_date); ?></strong>
      </div>
      <div>
        <small>Payment status</small>
        <strong class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label === 'Active' ? 'Up to date' : 'Not active'); ?></strong>
      </div>
    </div>
  </article>

  <article class="vc-subscription-card" id="vc-subscription-management">
    <div class="vc-subscription-card-header">
      <h3>Payment Method</h3>
      <span class="vc-subscription-card-link is-disabled">Gateway pending</span>
    </div>
    <div class="vc-subscription-empty">
      <p>No payment methods are available yet. This section will be connected once the payment gateway is configured.</p>
    </div>
  </article>

  <article class="vc-subscription-card">
    <div class="vc-subscription-card-header">
      <h3>Latest Invoices</h3>
      <?php if (!empty($invoice_items)): ?>
        <a class="vc-subscription-card-link" href="<?php echo esc_url($invoice_items[0]['url']); ?>">View latest</a>
      <?php endif; ?>
    </div>

    <?php if (empty($invoice_items)): ?>
      <div class="vc-subscription-empty">
        <p>No invoices found yet.</p>
      </div>
    <?php else: ?>
      <div class="vc-subscription-invoice-list">
        <?php foreach ($invoice_items as $invoice): ?>
          <a class="vc-subscription-invoice-item" href="<?php echo esc_url($invoice['url']); ?>">
            <div class="vc-subscription-invoice-copy">
              <strong><?php echo esc_html($invoice['code']); ?></strong>
              <span><?php echo esc_html($invoice['date']); ?> · <?php echo esc_html($invoice['status']); ?></span>
            </div>
            <span class="vc-subscription-invoice-amount"><?php echo esc_html($invoice['amount']); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>

  <article class="vc-subscription-card">
    <div class="vc-subscription-card-header">
      <h3>Subscription Management</h3>
    </div>
    <div class="vc-subscription-action-list">
      <a class="vc-subscription-action-item" href="<?php echo esc_url($levels_url); ?>">
        <div>
          <strong>Modify plan</strong>
          <span>Change between the available membership plans.</span>
        </div>
        <span>›</span>
      </a>
      <a class="vc-subscription-action-item" href="<?php echo esc_url($cancel_url); ?>">
        <div>
          <strong>Cancel subscription</strong>
          <span>Your access remains active until the current period ends.</span>
        </div>
        <span>›</span>
      </a>
    </div>
  </article>
</section>
