{{-- resources/views/domains/scripts.blade.php --}}
<script>
  let editingId = null;

//   $.ajaxSetup({
//     headers: {
//       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//     }
//   });

  function addDomain() {
    const name = $('#domainName').val().trim();

    if (!name) {
      alert('الرجاء إدخال اسم المجال');
      return;
    }

    $.post("{{ route('domains.store') }}", { name })
      .done(() => location.reload())
      .fail(xhr => {
        alert(xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : 'حدث خطأ');
      });
  }

  function editDomain(id) {
    editingId = id;
    const row = $(`tr[data-id='${id}']`);
    const name = row.find('td').eq(1).text().trim(); // الاسم في العمود الثاني

    $('#editDomainName').val(name);
    new bootstrap.Modal(document.getElementById('editModal')).show();
  }

  function saveEdit() {
    const name = $('#editDomainName').val().trim();
    if (!name) {
      alert('اسم المجال لا يمكن أن يكون فارغًا');
      return;
    }

    $.ajax({
      url: `/domains/${editingId}`,
      method: 'PUT',
      data: { name },
    })
    .done(() => location.reload())
    .fail(xhr => {
      alert(xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : 'حدث خطأ');
    });
  }

  function toggleActivation(id) {
    $.ajax({
      url: `/domains/${id}/toggle-status`,
      method: 'PATCH',
    })
    .done(() => location.reload())
    .fail(() => alert('حدث خطأ في تغيير الحالة'));
  }

  function confirmDelete(id) {
    Swal.fire({
      title: 'تأكيد العملية',
      text: 'هل أنت متأكد من حذف هذا المجال؟',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'نعم',
      cancelButtonText: 'لا',
      reverseButtons: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/domains/${id}`,
          method: 'DELETE'
        })
        .done(() => location.reload())
        .fail(() => alert('حدث خطأ في الحذف'));
      }
    });
  }

  $('#searchBox').on('input', function () {
    const val = $(this).val().toLowerCase();
    $('#tableBody tr').each(function () {
      const text = $(this).text().toLowerCase();
      $(this).toggle(text.includes(val));
    });
  });
</script>
