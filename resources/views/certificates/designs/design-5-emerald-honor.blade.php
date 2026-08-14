<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate – Emerald Honor & Gold Foil</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #022c22;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}
.cert-wrap {
    width: 297mm;
    min-height: 210mm;
    background: #064e3b;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 0 3mm #064e3b, 0 0 0 5mm #d97706, 0 20px 60px rgba(0,0,0,0.6);
}
.cert-bg-glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 30%, #04785722 0%, transparent 70%), radial-gradient(ellipse 60% 40% at 50% 80%, #d977060a 0%, transparent 60%);
    z-index: 0;
}
.cert-bg-texture {
    position: absolute;
    inset: 0;
    background-image: repeating-linear-gradient(0deg, rgba(217,119,6,0.03) 0px, rgba(217,119,6,0.03) 1px, transparent 1px, transparent 4px), repeating-linear-gradient(90deg, rgba(217,119,6,0.015) 0px, rgba(217,119,6,0.015) 1px, transparent 1px, transparent 6px);
    z-index: 0;
}
.frame-outer { position: absolute; top: 6mm; left: 6mm; right: 6mm; bottom: 6mm; border: 3pt solid #d97706; z-index: 1; }
.frame-inner { position: absolute; top: 9.5mm; left: 9.5mm; right: 9.5mm; bottom: 9.5mm; border: 1.5pt solid #f59e0b; z-index: 1; }
.frame-hairline { position: absolute; top: 11.5mm; left: 11.5mm; right: 11.5mm; bottom: 11.5mm; border: 0.5pt solid #f59e0b44; z-index: 1; }
.corner-dot { position: absolute; width: 6mm; height: 6mm; border-radius: 50%; background: radial-gradient(circle, #f59e0b 30%, #064e3b 100%); border: 1pt solid #d97706; z-index: 3; }
.corner-dot.tl { top: 4.5mm; left: 4.5mm; }
.corner-dot.tr { top: 4.5mm; right: 4.5mm; }
.corner-dot.bl { bottom: 4.5mm; left: 4.5mm; }
.corner-dot.br { bottom: 4.5mm; right: 4.5mm; }
.arch-band { position: absolute; top: 0; left: 0; right: 0; height: 4mm; background: repeating-linear-gradient(90deg, #064e3b 0px, #064e3b 6px, #d97706 6px, #d97706 8px, #064e3b 8px, #064e3b 14px); z-index: 2; }
.arch-band-bottom { position: absolute; bottom: 0; left: 0; right: 0; height: 4mm; background: repeating-linear-gradient(90deg, #064e3b 0px, #064e3b 6px, #d97706 6px, #d97706 8px, #064e3b 8px, #064e3b 14px); z-index: 2; }
.medallion-wrap { display: flex; justify-content: center; margin-bottom: 3mm; position: relative; z-index: 5; }
.medallion { width: 26mm; height: 26mm; border-radius: 50%; border: 2.5pt solid #f59e0b; background: radial-gradient(circle, #064e3b 40%, #022c22 100%); display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 0 1.5pt #d97706, inset 0 0 8px rgba(245,158,11,0.3); }
.medallion-star { font-size: 18pt; color: #f59e0b; text-shadow: 0 0 8px rgba(245,158,11,0.6); }
.cert-content { position: relative; z-index: 5; padding: 14mm 18mm; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: space-between; color: #ecfdf5; }
.school-title { font-size: 20pt; font-weight: 900; color: #f59e0b; text-transform: uppercase; letter-spacing: 1.5pt; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
.school-sub { font-size: 9.5pt; color: #a7f3d0; text-transform: uppercase; letter-spacing: 1pt; margin-top: 2px; }
.cert-heading { font-size: 26pt; font-weight: 900; color: #ffffff; text-transform: uppercase; margin-top: 6px; letter-spacing: 1pt; text-shadow: 0 2px 6px rgba(0,0,0,0.6); }
.recipient-name { font-size: 24pt; font-weight: 900; color: #f59e0b; border-bottom: 2pt solid #f59e0b; display: inline-block; padding: 2px 28px; margin: 10px 0; text-shadow: 0 2px 4px rgba(0,0,0,0.4); }
.body-desc { font-size: 11pt; color: #d1fae5; line-height: 1.65; max-width: 820px; margin: 0 auto; }
.footer-row { display: flex; justify-content: space-between; align-items: flex-end; padding-top: 12px; border-top: 1pt solid #047857; }
.gold-seal { width: 46px; height: 46px; border-radius: 50%; background: radial-gradient(circle, #fbbf24, #d97706); color: #022c22; font-weight: 900; font-size: 6.5pt; border: 2pt solid #fef3c7; display: flex; align-items: center; justify-content: center; text-align: center; text-transform: uppercase; box-shadow: 0 4px 12px rgba(0,0,0,0.4); }
</style>
</head>
<body>
<div class="cert-wrap">
    <div class="cert-bg-glow"></div>
    <div class="cert-bg-texture"></div>
    <div class="arch-band"></div>
    <div class="arch-band-bottom"></div>
    <div class="frame-outer"></div>
    <div class="frame-inner"></div>
    <div class="frame-hairline"></div>
    <div class="corner-dot tl"></div>
    <div class="corner-dot tr"></div>
    <div class="corner-dot bl"></div>
    <div class="corner-dot br"></div>

    <div class="cert-content">
        <div>
            <div class="medallion-wrap">
                <div class="medallion">
                    <span class="medallion-star">&#9733;</span>
                </div>
            </div>
            <div class="school-title">GENESIS GLOBAL SCHOOL</div>
            <div class="school-sub">Siraha, Nepal &middot; Excellence in Education</div>
            <div class="cert-heading">Certificate of Excellence</div>
        </div>

        <div>
            <div style="font-size: 10pt; color: #a7f3d0; text-transform: uppercase; letter-spacing: 1.5pt;">PROUDLY PRESENTED TO</div>
            <div class="recipient-name">AARAV SHRESTHA</div>
            <div class="body-desc">
                For demonstrating outstanding academic performance, exemplary character, and a steadfast commitment to excellence during the academic year 2081-82.
            </div>
        </div>

        <div class="footer-row">
            <div style="text-align: left;">
                <div style="font-size: 9pt; font-weight: bold; color: #f59e0b;">NO: CERT-2082-0051</div>
                <div style="font-size: 8.5pt; color: #a7f3d0;">Date: {{ date('d M Y') }}</div>
            </div>
            <div class="gold-seal">OFFICIAL<br>GOLD SEAL</div>
            <div style="text-align: right;">
                <div style="font-size: 10pt; font-weight: bold; color: #ffffff;">Sunil Yadav</div>
                <div style="font-size: 8.5pt; color: #a7f3d0; border-top: 1pt solid #f59e0b; padding-top: 2px;">Principal</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
