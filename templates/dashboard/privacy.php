<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-privacy-help">

  <section class="vc-privacy-help-card">
    <?php foreach ($faq_sections as $faq_section): ?>
      <div class="vc-privacy-help-section">
        <span class="vc-privacy-help-section-title"><?php echo esc_html($faq_section['label']); ?></span>
        <ol class="vc-privacy-help-list">
          <?php foreach ($faq_section['questions'] as $faq_question): ?>
            <li><?php echo esc_html($faq_question); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>
    <?php endforeach; ?>
    <nav class="vc-privacy-help-legal">
      <a class="vc-privacy-help-link" href="<?php echo esc_url($privacy_policy_url); ?>">Privacy Policy</a>
      <a class="vc-privacy-help-link" href="<?php echo esc_url($cookie_policy_url); ?>">Cookie Policy</a>
      <a class="vc-privacy-help-link" href="<?php echo esc_url($terms_url); ?>">Terms and conditions</a>
    </nav>
  </section>

  <section class="vc-privacy-help-card">
    <h2 class="vc-privacy-help-card-title">Contact</h2>
    <div class="vc-privacy-help-contact-group">
      <p class="vc-privacy-help-contact-label">Send a message</p>
      <a class="vc-privacy-help-mail" href="mailto:info@aeropro.academy">info@aeropro.academy</a>
    </div>
  </section>
</div>
