/* ---------------------------------------------------------
   تحويل التاريخ من ميلادي إلى هجري بدون انترنت
   جاهز للاستخدام في Laravel أو أي مشروع آخر
---------------------------------------------------------- */

function gregorianToHijri(gY, gM, gD) {
    if (typeof HijriConverter !== 'undefined') {
        return HijriConverter.gregorianToHijri(`${gY}-${gM}-${gD}`);
    }
    // Fallback logic
    var jd = Math.floor((1461 * (gY + 4800 + Math.floor((gM - 14) / 12))) / 4)
        + Math.floor((367 * (gM - 2 - 12 * (Math.floor((gM - 14) / 12)))) / 12)
        - Math.floor((3 * (Math.floor((gY + 4900 + Math.floor((gM - 14) / 12)) / 100))) / 4)
        + gD - 32075;

    var l = jd - 1948440 + 10632;
    var n = Math.floor((l - 1) / 10631);
    l = l - 10631 * n + 354;

    var j = (Math.floor((10985 - l) / 5316)) *
        (Math.floor((50 * l) / 17719)) +
        (Math.floor(l / 5670)) *
        (Math.floor((43 * l) / 15238));

    l = l - (Math.floor((30 - j) / 15)) *
        (Math.floor((17719 * j) / 50)) -
        (Math.floor(j / 16)) *
        (Math.floor((15238 * j) / 43)) + 29;

    var hM = Math.floor((24 * l) / 709);
    var hD = l - Math.floor((709 * hM) / 24);
    var hY = 30 * n + j - 30;

    return {
        year: hY,
        month: hM,
        day: hD
    };
}

function formatHijri(h) {
    if (typeof HijriConverter !== 'undefined') {
        return HijriConverter.formatHijri(h);
    }
    if (!h) return '';
    if (typeof h === 'string') return h;
    return (
        String(h.day).padStart(2, "0") +
        "/" +
        String(h.month).padStart(2, "0") +
        "/" +
        h.year
    );
}

/* ---------------------------------------------------------
   دالة تحويل تلقائي من حقل ميلادي إلى حقل هجري
---------------------------------------------------------- */
function bindGregorianToHijri(gregorianInputId, hijriInputId) {
    document.getElementById(gregorianInputId).addEventListener("change", function () {
        let parts = this.value.split("-");
        if (parts.length !== 3) return;

        let gy = parseInt(parts[0]);
        let gm = parseInt(parts[1]);
        let gd = parseInt(parts[2]);

        let h = gregorianToHijri(gy, gm, gd);
        document.getElementById(hijriInputId).value = formatHijri(h);
    });
}
