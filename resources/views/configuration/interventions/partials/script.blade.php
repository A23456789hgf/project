<script>
    document.addEventListener('DOMContentLoaded', function () {
        const domainSelect = document.getElementById('domain_id');
        const subdomainSelect = document.getElementById('subdomain_id');

        function loadSubdomains(domainId, selectedSubdomainId = null) {
            if (!domainId) {
                subdomainSelect.innerHTML = '<option value="">اختر المجال الفرعي</option>';
                return;
            }

            subdomainSelect.innerHTML = '<option value="">جاري التحميل...</option>';

            fetch(`/interventions/get-subdomains/${domainId}`)
                .then(res => res.json())
                .then(data => {
                    subdomainSelect.innerHTML = '<option value="">اختر المجال الفرعي</option>';
                    data.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = sub.name;
                        if (selectedSubdomainId && selectedSubdomainId == sub.id) {
                            option.selected = true;
                        }
                        subdomainSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading subdomains:', error);
                    subdomainSelect.innerHTML = '<option value="">خطأ في تحميل المجالات الفرعية</option>';
                });
        }

        if (domainSelect) {
            domainSelect.addEventListener('change', function () {
                if (this.value) {
                    loadSubdomains(this.value);
                } else {
                    subdomainSelect.innerHTML = '<option value="">اختر المجال الفرعي</option>';
                }
            });

            if (domainSelect.value) {
                loadSubdomains(domainSelect.value);
            }
        }
    });
</script>
