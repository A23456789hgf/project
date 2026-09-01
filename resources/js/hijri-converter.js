/**
 * Hijri-Gregorian Date Converter & UI Initializer
 * Consolidated version for project forms.
 */
class HijriConverter {
    /**
     * Convert Gregorian date components to Julian Day.
     */
    static gregorianToJD(year, month, day) {
        if (month <= 2) {
            year -= 1;
            month += 12;
        }
        let a = Math.floor(year / 100);
        let b = 2 - a + Math.floor(a / 4);
        return Math.floor(365.25 * (year + 4716)) + Math.floor(30.6001 * (month + 1)) + day + b - 1524.5;
    }

    /**
     * Convert Julian Day to Hijri date components (Tabular Islamic Calendar).
     */
    static jdToHijri(jd) {
        jd = jd + 0.5;
        let i = Math.floor(jd);
        let l = i - 1948440 + 10632;
        let n = Math.floor((l - 1) / 10631);
        l = l - 10631 * n + 354;
        let j = (Math.floor((10985 - l) / 5316)) * (Math.floor((50 * l) / 17719)) + (Math.floor(l / 5670)) * (Math.floor((43 * l) / 15238));
        l = l - (Math.floor((30 - j) / 15)) * (Math.floor((17719 * j) / 50)) - (Math.floor(j / 16)) * (Math.floor((15238 * j) / 43)) + 29;
        let m = Math.floor((24 * l) / 709);
        let d = l - Math.floor((709 * m) / 24);
        let y = 30 * n + j - 30;
        return { year: y, month: m, day: d };
    }

    /**
     * Main conversion method for UI (accepts YYYY-MM-DD string or Date object).
     */
    static gregorianToHijri(dateStr) {
        if (!dateStr) return null;

        let year, month, day;

        if (dateStr instanceof Date) {
            year = dateStr.getFullYear();
            month = dateStr.getMonth() + 1;
            day = dateStr.getDate();
        } else {
            const parts = dateStr.split('-');
            if (parts.length !== 3) return null;
            year = parseInt(parts[0]);
            month = parseInt(parts[1]);
            day = parseInt(parts[2]);
        }

        const jd = this.gregorianToJD(year, month, day);
        return this.jdToHijri(jd);
    }

    /**
     * Convert Julian Day to Gregorian date components.
     */
    static jdToGregorian(jd) {
        let l = Math.floor(jd + 68569.5);
        let n = Math.floor((4 * l) / 146097);
        l = l - Math.floor((146097 * n + 3) / 4);
        let i = Math.floor((4000 * (l + 1)) / 1461001);
        l = l - Math.floor((1461 * i) / 4) + 31;
        let j = Math.floor((80 * l) / 2447);
        let d = l - Math.floor((2447 * j) / 80);
        l = Math.floor(j / 11);
        let m = j + 2 - 12 * l;
        let y = 100 * (n - 49) + i + l;
        return { year: y, month: m, day: d };
    }

    /**
     * Convert Hijri date components to Julian Day.
     */
    static hijriToJD(year, month, day) {
        return Math.floor((11 * year + 3) / 30) + 354 * year + 30 * month - Math.floor((month - 1) / 2) + day + 1948440 - 385;
    }

    /**
     * Convert Hijri date string (day/month/year) to Gregorian date components.
     */
    static hijriToGregorian(dateStr) {
        if (!dateStr) return null;
        const parts = dateStr.split('/');
        if (parts.length !== 3) return null;

        const day = parseInt(parts[0]);
        const month = parseInt(parts[1]);
        const year = parseInt(parts[2]);

        const jd = this.hijriToJD(year, month, day);
        return this.jdToGregorian(jd);
    }

    /**
     * Format Hijri object as day/month/year.
     * Defensive: Handles string inputs by returning them as-is if they look like dates.
     */
    static formatHijri(hijri) {
        if (!hijri) return '';
        if (typeof hijri === 'string') return hijri;

        const d = String(hijri.day).padStart(2, '0');
        const m = String(hijri.month).padStart(2, '0');
        return `${d}/${m}/${hijri.year}`;
    }

    /**
     * Calculate duration in days between two Gregorian dates.
     */
    static calculateDuration(startDateValue, endDateValue) {
        if (startDateValue && endDateValue) {
            const start = new Date(startDateValue + 'T00:00:00');
            const end = new Date(endDateValue + 'T00:00:00');
            const diffTime = end.getTime() - start.getTime();
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
            return diffDays >= 0 ? diffDays : 0;
        }
        return '';
    }

    /**
     * Initialize UI logic for a specific row/card.
     */
    static initializeActionCard(card) {
        // Classes used in Executive tables
        const startDateInput = card.querySelector('.executive-start-date');
        const startHijriInput = card.querySelector('.executive-start-date-hijri');
        const endDateInput = card.querySelector('.executive-end-date');
        const endHijriInput = card.querySelector('.executive-end-date-hijri');
        const durationInput = card.querySelector('.executive-duration');

        const updateStart = () => {
            if (startDateInput && startHijriInput) {
                const hijri = this.gregorianToHijri(startDateInput.value);
                startHijriInput.value = this.formatHijri(hijri);
                
                // Silent validation: update min of end date
                if (endDateInput) endDateInput.setAttribute('min', startDateInput.value);

                if (durationInput && endDateInput?.value) {
                    durationInput.value = this.calculateDuration(startDateInput.value, endDateInput.value);
                }
            }
        };

        const updateEnd = () => {
            if (endDateInput && endHijriInput) {
                const hijri = this.gregorianToHijri(endDateInput.value);
                endHijriInput.value = this.formatHijri(hijri);
                
                // Silent validation: update max of start date
                if (startDateInput) startDateInput.setAttribute('max', endDateInput.value);

                if (durationInput && startDateInput?.value) {
                    durationInput.value = this.calculateDuration(startDateInput.value, endDateInput.value);
                }
            }
        };

        if (startDateInput) startDateInput.addEventListener('change', updateStart);
        if (endDateInput) endDateInput.addEventListener('change', updateEnd);

        // Initial update
        updateStart();
        updateEnd();
    }
}

// Global exposure
window.HijriConverter = HijriConverter;
window.ExecutiveActionRowInitializer = HijriConverter.initializeActionCard.bind(HijriConverter);

// Auto-initialize on DOM load for existing elements with the trigger class
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.executive-activity-action-row').forEach(card => {
        if (!card.dataset.initialized) {
            HijriConverter.initializeActionCard(card);
            card.dataset.initialized = 'true';
        }
    });
});
