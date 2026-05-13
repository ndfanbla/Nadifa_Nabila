$(document).ready(function () {
  loadData();

  $("#serviceForm").on("submit", function (e) {
    e.preventDefault();
    let formData = {
      id: $("#editId").val(),
      tanggal: $("#tanggal").val(),
      ruangan: $("#ruangan").val(),
      prasarana: $("#prasarana").val(),
      merk: $("#merk").val(),
      model: $("#model").val(),
      sn_code: $("#sn_code").val(),
      last_calibration: $("#last_calibration").val(),
      distributor: $("#distributor").val(),
      year: $("#year").val(),
      physical_test: $("#chk_physical").is(":checked") ? 1 : 0,
      function_test: $("#chk_function").is(":checked") ? 1 : 0,
      accessories_check: $("#chk_accessories").is(":checked") ? 1 : 0,
      parameter_setting: $("#chk_parameter").is(":checked") ? 1 : 0,
      mechanical_check: $("#chk_mechanical").is(":checked") ? 1 : 0,
      warming_check: $("#chk_warming").is(":checked") ? 1 : 0,
      troubleshooting: $("#chk_trouble").is(":checked") ? 1 : 0,
      problem_solution: $("#problem_solution").val(),
      description: $("#description").val(),
      result_status: $('input[name="hasil"]:checked').val(),
    };

    $.ajax({
      url: "api.php",
      type: "POST",
      data: JSON.stringify(formData),
      contentType: "application/json",
      success: function (response) {
        if (response.success) {
          alert(response.message);
          resetForm();
          loadData();
        } else {
          alert("Error: " + response.message);
        }
      },
    });
  });

  $("#cancelBtn").click(function () {
    resetForm();
  });
});

function loadData() {
  $.ajax({
    url: "api.php",
    type: "GET",
    success: function (response) {
      if (response.success) {
        $("#totalCount").text(response.data.length);
        renderTable(response.data);
      }
    },
  });
}

function renderTable(data) {
  let html = "";
  data.forEach((row) => {
    html += `<tr>
            <td>${row.report_number}</td>
            <td>${row.report_date}</td>
            <td>${row.room_name}</td>
            <td>${row.facility_type}</td>
            <td>${row.brand}/${row.model}</td>
            <td>${row.result_status}</td>
            <td class="action-icons">
                <i class="fas fa-print" onclick="printReport(${row.id})"></i>
                <i class="fas fa-edit" onclick="editReport(${row.id})"></i>
                <i class="fas fa-trash-alt" onclick="deleteReport(${row.id})"></i>
            </td>
        </tr>`;
  });
  $("#tableBody").html(html || '<tr><td colspan="7">Tidak ada data</td></tr>');
}

function editReport(id) {
  $.ajax({
    url: "api.php?id=" + id,
    type: "GET",
    success: function (response) {
      if (response.success) {
        let d = response.data;
        $("#editId").val(d.id);
        $("#tanggal").val(d.report_date);
        $("#ruangan").val(d.room_name);
        $("#prasarana").val(d.facility_type);
        $("#merk").val(d.brand);
        $("#model").val(d.model);
        $("#sn_code").val(d.sn_code);
        $("#last_calibration").val(d.last_calibration);
        $("#distributor").val(d.distributor);
        $("#year").val(d.year);
        $("#chk_physical").prop("checked", d.physical_test == 1);
        $("#chk_function").prop("checked", d.function_test == 1);
        $("#chk_accessories").prop("checked", d.accessories_check == 1);
        $("#chk_parameter").prop("checked", d.parameter_setting == 1);
        $("#chk_mechanical").prop("checked", d.mechanical_check == 1);
        $("#chk_warming").prop("checked", d.warming_check == 1);
        $("#chk_trouble").prop("checked", d.troubleshooting == 1);
        $("#problem_solution").val(d.problem_solution);
        $("#description").val(d.description);
        $(`input[name="hasil"][value="${d.result_status}"]`).prop(
          "checked",
          true,
        );
        $("#formTitle").text("Edit Service Report");
        $("html, body").animate({ scrollTop: 0 }, 500);
      }
    },
  });
}

function deleteReport(id) {
  if (confirm("Hapus laporan ini?")) {
    $.ajax({
      url: "api.php?id=" + id,
      type: "DELETE",
      success: function (response) {
        if (response.success) {
          alert("Terhapus");
          loadData();
        } else alert("Gagal hapus");
      },
    });
  }
}

function printReport(id) {
  window.open("print.php?id=" + id, "_blank", "width=900,height=700");
}

function resetForm() {
  $("#editId").val("");
  $("#serviceForm")[0].reset();
  $("#tanggal").val(new Date().toISOString().slice(0, 10));
  $("#formTitle").text("Tambah Service Report");
}
