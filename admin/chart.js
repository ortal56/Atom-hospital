$(document).ready(function() {
    $.post("admin.php", 
        { action: "chart", csrf_token: $('input[name="csrf_token"]').val() },
        function(response) {
            let data = response;
            employees(data.Doctor,data.Doctor_assist,data.Nurse,data.receptionist,data.laboratory,data.Admin);
            pations(data.patients);
    });
});



function employees(Doctor,Doctor_assist,Nurse,Receptionist,laboratory,Admin) {
    const xValues = ["Doctor", "Doctor assist", "Nurse", "Receptionist","laboratory", "Admin"];
    const yValues = [Doctor, Doctor_assist, Nurse, Receptionist,laboratory, Admin];
    const barColors = ["red", "green","blue","orange","white","brown"];

    new Chart("employees", {
  type: "bar",
  data: {
    labels: xValues,
    datasets: [{
      backgroundColor: barColors,
      data: yValues
    }]
  },
  options: {
    legend: {display: false},
    title: {
      display: true,
      text: "ATOM Hospital Employees",
        fontColor: "white"

    }
  }
});
}


function pations(pation){
    const xValues = ["patients"];
    const yValues = [pation];
    const barColors = ["purple"];

    new Chart("patients", {
        type: "bar",
        data: {
            labels: xValues,
            datasets: [{
                backgroundColor: barColors,
                data: yValues
            }]
        },
        options: {
            legend: { display: false },
            title: {
                display: true,
                text: "ATOM Hospital patients",
                fontColor: "white"
            }
        }
    });
    
}
