$(document).ready(function() {
    $(".home").show();
    $(".addUserForm").hide();
    $(".searchForm").hide();
    $(".Users_List").hide();
    $(".pation").hide();
    $(".doctorAssist").hide();
});

$("a[href='#Home']").click(function(event) {
    event.preventDefault();
    $(".home").show();
    $(".addUserForm").hide();
    $(".searchForm").hide();
    $(".Users_List").hide();
    $(".pation").hide();
    $(".doctorAssist").hide();
});

$("a[href='#Users_List']").click(function(event) {
    event.preventDefault();
    $(".addUserForm").hide();
    $(".searchForm").hide();
    $(".Users_List").show();
    $(".pation").hide();
    $(".home").hide();
    $(".doctorAssist").hide();
});

$("a[href='#addUserForm']").click(function(event) {
    event.preventDefault();
    $(".addUserForm").show();
    $(".searchForm").hide();
    $(".Users_List").hide();
    $(".pation").hide();
    $(".home").hide();
    $(".doctorAssist").hide();
});

$("a[href='#searchForm']").click(function(event) {
    event.preventDefault();
    $(".addUserForm").hide();
    $(".searchForm").show();
    $(".Users_List").hide();
    $(".pation").hide();
    $(".home").hide();
    $(".doctorAssist").hide();
});

$("a[href='#pation']").click(function(event) {
    event.preventDefault();
    $(".addUserForm").hide();
    $(".searchForm").hide();
    $(".Users_List").hide();
    $(".pation").show();
    $(".home").hide();
    $(".doctorAssist").hide();
});
$("a[href='#doctorAssist']").click(function(event) {
    event.preventDefault();
    $(".addUserForm").hide();
    $(".searchForm").hide();
    $(".Users_List").hide();
    $(".pation").hide();
    $(".home").hide();
    $(".doctorAssist").show();
    getopt();
});
function togglePassword(id, btn) {
    const password = document.getElementById(`password-${id}`);
    password.style.display = password.style.display === 'none' ? 'inline' : 'none';
    btn.style.display = btn.style.display === 'none' ? 'inline' : 'none';
}

$("#addUserForm").submit(function(event) {
    event.preventDefault();
    $.post("admin.php", {
        action: "add",
        name: $("#name").val(),
        password: $("#password").val(),
        status: $("#status").val(),
        role: $("#role").val(),
        csrf_token: $('input[name="csrf_token"]').val()
    }, function(response) {
        Swal.fire({
            title: "User was added",
            text: $("#name").val(),
            icon: "success"
        });
    });
});

$("#searchForm").submit(function(event) {
    event.preventDefault();
    $.post("admin.php", {
        action: "search",
        name: $("#searchName").val(),
        status: $("#searchStatus").val(),
        role: $("#searchRole").val(),
        csrf_token: $('input[name="csrf_token"]').val()
    }, function(response) {
        const users = JSON.parse(response);
        updateTable(users);
    });
});

function deleteUser(id) {
    if (confirm("Are you sure you want to delete this user?")) {
        $.post("admin.php", { action: "delete", id: id, csrf_token: $('input[name="csrf_token"]').val() }, function(response) {
            location.reload();
        });
    }
}

async function editUser(id, name, status, role) {
    const { value: formValues } = await Swal.fire({
        title: 'Edit User',
        html:
            `<input id="swal-input1" class="swal2-input" placeholder="Name" value="${name}">` +
            `<input id="swal-input2" class="swal2-input" placeholder="Status" value="${status}">` +
            `<input id="swal-input3" class="swal2-input" placeholder="Role" value="${role}">`,
        focusConfirm: false,
        preConfirm: () => {
            return [
                document.getElementById('swal-input1').value,
                document.getElementById('swal-input2').value,
                document.getElementById('swal-input3').value
            ]
        }
    });

    if (formValues) {
        const [newName, newStatus, newRole] = formValues;
        if (newName && newStatus && newRole) {
            $.post("admin.php", {
                action: "update",
                id: id,
                name: newName,
                status: newStatus,
                role: newRole,
                csrf_token: $('input[name="csrf_token"]').val()
            }, function(response) {
                Swal.fire({
                    title: "Details updated successfully!",
                    icon: "success"
                });
            });
        }
    }
}

function updateTable(users) {
    const tableBody = $(".uptodate");
    tableBody.empty();
    tableBody.append(
        `
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        `
    )
    users.forEach(user => {
        const row = `<tr>
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.status}</td>
            <td>${user.role}</td>
            <td>
                <button onclick="editUser(${user.id}, '${user.name}', '${user.status}', '${user.role}')">Edit</button>
                <button onclick="deleteUser(${user.id})">Delete</button>
            </td>
        </tr>`;
        tableBody.append(row);
    });
}

function deletepation(id) {
    if (confirm("Are you sure you want to delete this user?")) {
        $.post("admin.php", { action: "deletepation", id: id, csrf_token: $('input[name="csrf_token"]').val() }, function(response) {
            location.reload();
        });
    }
}
function getopt() {
    $.post("admin.php", { action: "getassist", csrf_token: $('input[name="csrf_token"]').val() }, function(response) {
        let data = JSON.parse(response);
        for(let i = 0; i < data.length; i++) {
            if(data[i].role === "Doctor"){
                $("#Doctor_name").append(`
                    <option value="${data[i].name}">${data[i].name}</option>
                `);  // add doctor assistant to dropdown list
            } else {
                $("#assist_name").append(`
                    <option value="${data[i].name}">${data[i].name}</option>
                `);  // add patient to dropdown list
            }
        }
    });
}

$("#addassist").submit(function(event) {
    event.preventDefault();
    $.post("admin.php", {
        action: "addassist",
        Doctor_name: $("#Doctor_name").val(),
        assist_name: $("#assist_name").val(),
        csrf_token: $('input[name="csrf_token"]').val()
    }, function(response) {
        if(response === "Assistance added successfully!") {
            Swal.fire({
                title: "Doctor Assistant was added",
                text: $("#assistName").val(),
                icon: "success"
            });
            setTimeout(() => {
                location.reload();
            },1000)
        } else {
            Swal.fire({
                title: "Error adding Doctor Assistant",
                icon: "error"
            });
        }
    });
});

function deleteAssist(id) {
    if (confirm("Are you sure you want to remove this assistant?")) {
        $.post("admin.php", {
            action: "removeAssist",
            id: id,
            csrf_token: $('input[name="csrf_token"]').val()
        }, function(response) {
            if(response === "Assistance removed successfully!") {
                Swal.fire({
                    title: "Doctor Assistant was removed",
                    icon: "success"
                });
                setTimeout(() => {
                    location.reload();
                },1000)
                
            } else {
                Swal.fire({
                    title: "Error removing Doctor Assistant",
                    icon: "error"
                });
            }
        });
    }
}