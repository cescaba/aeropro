<?php
if (!defined('ABSPATH')) exit;

$vc_onboarding_radius = '8px';
$vc_onboarding_logo_url = VC_OW_PLUGIN_URL . 'templates/assets/aeropro-logo2.svg';
$vc_greeting_style = 'padding:0 0 8px; font-family:Poppins, Arial, Helvetica, sans-serif; font-size:18px; line-height:27px; font-weight:500; letter-spacing:0; color:#212121; text-align:center;';
$vc_description_style = 'padding:0 0 37px; font-family:Poppins, Arial, Helvetica, sans-serif; font-size:18px; line-height:100%; font-weight:500; letter-spacing:0; color:#666666; text-align:center;';
$vc_meta_style = 'font-family:Poppins, Arial, Helvetica, sans-serif; font-size:14px; line-height:14px; font-weight:400; letter-spacing:-0.15px; color:#666666; text-align:center;';
?>
<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="padding:0; background-color:#f3f4f6; font-family:Poppins, Arial, Helvetica, sans-serif; color:#212121;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    Please confirm your new email address.
  </div>

  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:770px; background-color:#ffffff; border-radius:16px; overflow:hidden;">
          <tr>
            <td style="padding:53px 40px; text-align:center; background-color:#ffffff;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                  <td align="center" style="padding:0 0 50px;">
                    <img
                      src="<?php echo esc_url($vc_onboarding_logo_url); ?>"
                      alt="<?php echo esc_attr($site_name); ?>"
                      width="180"
                      style="display:block; width:180px; max-width:100%; height:auto; border:0; outline:none; text-decoration:none;"
                    >
                  </td>
                </tr>
              </table>
              <div style="padding:0 0 23px; font-family:Poppins, Arial, Helvetica, sans-serif; font-size:28px; line-height:41px; font-weight:600; letter-spacing:0; color:#212121; text-align:center;">
                Verify your mail
              </div>

              <div style="<?php echo esc_attr($vc_greeting_style); ?>">
                Hi <?php echo esc_html($recipient_name); ?>,
              </div>

              <div style="<?php echo esc_attr($vc_description_style); ?>">
                Click the button below to verify your email and activate your 14-day trial.
              </div>

              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                  <td align="center" style="padding:0 0 42px;">
                    <a
                      href="<?php echo esc_url($confirm_url); ?>"
                      style="display:inline-block; padding:14px 28px; background-color:#1447E6; border-radius:<?php echo esc_attr($vc_onboarding_radius); ?>; color:#ffffff; font-size:16px; font-weight:700; line-height:1; text-decoration:none;"
                    >
                      Verify email
                    </a>
                  </td>
                </tr>
              </table>

              <div style="padding:0 0 9px; font-family:Poppins, Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; font-weight:400; letter-spacing:-0.15px; color:#666666; text-align:center;">
                If the button does not work, copy and paste this link into your browser:
              </div>

              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                  <td align="center" style="padding:0 0 8px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:520px;">
                      <tr>
                        <td style="font-family:Poppins, Arial, Helvetica, sans-serif; font-size:14px; line-height:14px; font-weight:400; letter-spacing:-0.15px; word-break:break-all; text-align:center;">
                          <a
                            href="<?php echo esc_url($confirm_url); ?>"
                            style="color:#3237B7; text-decoration:underline;"
                          >
                            <?php echo esc_html($confirm_url); ?>
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <div style="<?php echo esc_attr($vc_meta_style); ?>">
                If you didn't request this email, you can ignore it.
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
