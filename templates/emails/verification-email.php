<?php
if (!defined('ABSPATH')) exit;
?>
<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo esc_html($subject); ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#212121;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    Verify your email and activate your 14-day trial.
  </div>

  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden;">
          <tr>
            <td style="padding:40px 32px 24px; text-align:center; background-color:#ffffff;">
              <div style="font-size:28px; line-height:1.2; font-weight:700; color:#212121;"><?php echo esc_html($site_name); ?></div>
            </td>
          </tr>

          <tr>
            <td style="padding:0 32px 40px;">
              <h1 style="margin:0 0 16px; font-size:30px; line-height:1.2; font-weight:700; color:#212121; text-align:center;">
                Verify your email
              </h1>

              <p style="margin:0 0 16px; font-size:16px; line-height:1.6; color:#4b5563;">
                Hi <?php echo esc_html($recipient_name); ?>,
              </p>

              <p style="margin:0 0 16px; font-size:16px; line-height:1.6; color:#4b5563;">
                Click the button below to verify your email and activate your 14-day trial.
              </p>

              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:32px 0;">
                <tr>
                  <td align="center">
                    <a href="<?php echo esc_url($verify_url); ?>" style="display:inline-block; padding:14px 28px; background-color:#1447E6; border-radius:8px; color:#ffffff; font-size:16px; font-weight:700; line-height:1; text-decoration:none;">
                      Verify email
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 12px; font-size:14px; line-height:1.6; color:#6b7280;">
                If the button does not work, copy and paste this link into your browser:
              </p>

              <p style="margin:0 0 24px; font-size:14px; line-height:1.6; word-break:break-all;">
                <a href="<?php echo esc_url($verify_url); ?>" style="color:#1447E6; text-decoration:underline;"><?php echo esc_html($verify_url); ?></a>
              </p>

              <p style="margin:0; font-size:14px; line-height:1.6; color:#6b7280;">
                If you didn't request this email, you can ignore it.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
