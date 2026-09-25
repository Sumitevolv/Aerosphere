AEROSPHERE — HOSTINGER READY WEBSITE

Pages:
- index.html
- why-choose-us.html
- products.html
- capabilities.html

Assets:
- assets/ contains the approved website/product images.

Catalogue:
- aerosphere_complete_catalogue.pdf (compressed for web/email use)

Contact form:
- The website sends the enquiry to /api/send-catalogue.php.
- That PHP file sends the catalogue through Resend.
- Before going live, edit api/config.php and add:
  1) your Resend API key
  2) a verified sender email on your domain
- Never put the Resend API key inside any HTML/JS file.

WhatsApp:
- After successful catalogue email delivery, the enquiry opens WhatsApp to +91 93260 99879.

HOSTINGER:
- This package is designed for normal PHP web hosting using public_html.
- Upload/extract the contents so index.html, the 3 other HTML pages, assets/, api/, and the PDF sit directly inside public_html.


2026-09 website update: The enquiry form is now a native Netlify Form named aerosphere-enquiry. After deploying, in Netlify go to Project configuration -> Notifications -> Emails and add a form submission notification for this form to aerosphere077@gmail.com. This is required for email notifications; submissions are still collected by Netlify Forms without it.
