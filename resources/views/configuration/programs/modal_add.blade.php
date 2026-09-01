<!-- زر فتح المودال -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
  إضافة برنامج جديد
</button>

<!-- مودال إضافة البرنامج -->
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="programFormModal">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addProgramModalLabel">إضافة برنامج</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="nameModal" class="form-label">اسم البرنامج</label>
            <input type="text" name="name" id="nameModal" class="form-control" required>
          </div>
          <div id="modalError" class="text-danger"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">حفظ</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#programFormModal').submit(function(e) {
    e.preventDefault();

    // مسح رسالة الخطأ القديمة
    $('#modalError').text('');

    $.ajax({
      url: "{{ route('programs.store') }}",
      method: "POST",
      data: $(this).serialize(),
      success: function(program) {
        // إغلاق المودال
        var modal = bootstrap.Modal.getInstance(document.getElementById('addProgramModal'));
        modal.hide();

        // إضافة البرنامج الجديد للـ select واختياره
        $('#program').append(new Option(program.name, program.id, true, true));

        // إعادة تعيين الفورم داخل المودال
        $('#programFormModal')[0].reset();
      },
      error: function(xhr) {
        let errMsg = 'حدث خطأ غير متوقع';
        if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.name) {
          errMsg = xhr.responseJSON.errors.name.join(', ');
        }
        $('#modalError').text(errMsg);
      }
    });
  });
});
</script>
