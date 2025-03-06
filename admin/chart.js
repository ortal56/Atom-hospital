$(document).ready(function() {
    $.post("admin.php", 
        { action: "chart" },
        function(response) {
            let data = response;
            employees(data.Doctor,data.Doctor_assist,data.Nurse,data.receptionist,data.laboratory,data.Admin);
            pations(data.patients,data.archive);
            statu(data.alive,data.dead);
    });
});


function employees(Doctor,Doctor_assist,Nurse,Receptionist,laboratory,Admin) {
    const xValues = ["Doctor", "Doctor assist", "Nurse", "Receptionist","laboratory", "Admin"];
    const yValues = [Doctor, Doctor_assist, Nurse, Receptionist,laboratory, Admin];
    const barColors = ["red", "green","blue","orange","grey","brown"];

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


function pations(pation,archive){
    const xValues = ["patients","archive"];
    const yValues = [pation,archive];
    const barColors = ["purple","cyan"];

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

function statu(alive,dead){
  const xValues = ["Alive","Dead"];
    const yValues = [alive,dead];
    const barColors = ["white","black"];

    new Chart("statu", {
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
                text: "ATOM Hospital patients status",
                fontColor: "white"
            }
        }
    });
}