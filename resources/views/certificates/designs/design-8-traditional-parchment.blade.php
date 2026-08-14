<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate – Traditional Parchment & Royal Seal</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #451a03;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}
.cert-wrap {
    width: 297mm;
    min-height: 210mm;
    background: #fffdf5;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 0 3.5mm #78350f, 0 0 0 5.5mm #b45309, 0 20px 60px rgba(0,0,0,0.6);
}
.cert-bg-texture {
    position: absolute;
    inset: 0;
    background-image: repeating-linear-gradient(45deg, rgba(120,53,15,0.03) 0px, rgba(120,53,15,0.03) 2px, transparent 2px, transparent 8px), repeating-linear-gradient(-45deg, rgba(180,83,9,0.02) 0px, rgba(180,83,9,0.02) 2px, transparent 2px, transparent 8px);
    z-index: 0;
}
.frame-outer { position: absolute; top: 6mm; left: 6mm; right: 6mm; bottom: 6mm; border: 3.5pt solid #78350f; z-index: 1; }
.frame-inner { position: absolute; top: 9.5mm; left: 9.5mm; right: 9.5mm; bottom: 9.5mm; border: 1.5pt double #b45309; z-index: 1; }
.corner-bracket { position: absolute; width: 16mm; height: 16mm; border: 2pt solid #78350f; z-index: 2; }
.corner-bracket.tl { top: 5mm; left: 5mm; border-right: none; border-bottom: none; }
.corner-bracket.tr { top: 5mm; right: 5mm; border-left: none; border-bottom: none; }
.corner-bracket.bl { bottom: 5mm; left: 5mm; border-right: none; border-top: none; }
.corner-bracket.br { bottom: 5mm; right: 5mm; border-left: none; border-top: none; }
.medallion-wrap { display: flex; justify-content: center; margin-bottom: 2mm; position: relative; z-index: 5; }
.medallion { width: 24mm; height: 24mm; border-radius: 50%; border: 2pt solid #b45309; background: radial-gradient(circle, #fef3c7 40%, #fde68a 100%); display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 0 1pt #78350f; }
.medallion-star { font-size: 16pt; color: #78350f; }
.cert-content { position: relative; z-index: 5; padding: 14mm 18mm; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: space-between; color: #1c1917; }
.school-name { font-size: 21pt; font-weight: 900; color: #78350f; text-transform: uppercase; letter-spacing: 1.5pt; font-family: 'Times New Roman', serif; }
.school-name-ne { font-size: 14pt; font-weight: bold; color: #92400e; margin-top: 2px; }
.cert-title { font-size: 25pt; font-weight: 900; color: #78350f; text-transform: uppercase; margin-top: 6px; letter-spacing: 1pt; text-decoration: underline; }
.recipient-name { font-size: 24pt; font-weight: 900; color: #92400e; border-bottom: 2pt solid #78350f; display: inline-block; padding: 2px 28px; margin: 8px 0; }
.body-desc { font-size: 11pt; color: #292524; line-height: 1.65; max-width: 820px; margin: 0 auto; }
.footer-row { display: flex; justify-content: space-between; align-items: flex-end; padding-top: 12px; border-top: 1pt solid #d97706; }
.wax-stamp { width: 46px; height: 46px; border-radius: 50%; background: radial-gradient(circle, #b91c1c, #7f1d1d); color: #fef3c7; font-weight: 900; font-size: 6pt; border: 2pt solid #d97706; display: flex; align-items: center; justify-content: center; text-align: center; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
</style>
</head>
<body>
<div class="cert-wrap">
    <div class="cert-bg-texture"></div>
    <div class="frame-outer"></div>
    <div class="frame-inner"></div>
    <div class="corner-bracket tl"></div>
    <div class="corner-bracket tr"></div>
    <div class="corner-bracket bl"></div>
    <div class="corner-bracket br"></div>

    <div class="cert-content">
        <div>
            <div class="medallion-wrap">
                <div class="medallion">
                    <span class="medallion-star">&#9818;</span>
                </div>
            </div>
            <div class="school-name">GENESIS GLOBAL SCHOOL</div>
            <div class="school-name-ne">जेनेसेसि ग्लोबल स्कूल &middot; सिरहा, नेपाल</div>
            <div class="cert-title">Character Certificate</div>
            <div style="font-size: 11pt; font-weight: bold; color: #92400e; margin-top: 2px;">चारित्रिक प्रमाण-पत्र</div>
        </div>

        <div>
            <div style="font-size: 9.5pt; color: #78350f; text-transform: uppercase; letter-spacing: 1pt;">This is to certify that</div>
            <div class="recipient-name">AARAV SHRESTHA</div>
            <div class="body-desc">
                son/daughter of <strong>Sunil Shrestha</strong>, Admission No. <strong>1042</strong>, was a bonafide student of Class <strong>10</strong> during the academic year <strong>2081-82</strong>. During the period of study, the conduct and moral character of the student were found to be exemplary.
            </div>
        </div>

        <div class="footer-row">
            <div style="text-align: left;">
                <div style="font-size: 9pt; font-weight: bold; color: #78350f;">REF NO: 2082/084</div>
                <div style="font-size: 8.5pt; color: #78350f;">Date: {{ date('d M Y') }}</div>
            </div>
            <div class="wax-stamp">OFFICIAL<br>WAX SEAL</div>
            <div style="text-align: right;">
                <div style="font-size: 10pt; font-weight: bold; color: #78350f;">Sunil Yadav</div>
                <div style="font-size: 8.5pt; color: #78350f; border-top: 1pt solid #78350f; padding-top: 2px;">Principal / प्रधानाध्यापक</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
