function findWordInURL(word) {
    return document.URL.includes(word);
}
function get_dir(role) {
    if (role == 'Admin') {
        return findWordInURL("admin/admin.php");
    } 
    else if(role == 'Doctor'){
        return findWordInURL("workspace/patient.html");
    }
    else if(role == 'Doctor_assist'){
        return findWordInURL("workspace/assistpatient.html");
    }
    else if(role == 'Nurse'){
        return findWordInURL("workspace/nurse.html");
    }
    else if(role == 'receptionist'){
        return findWordInURL("workspace/receptionist.html");
    }
    else if(role == 'laboratory'){
        return findWordInURL("workspace/laboratory.html");
    }
    else{
        return "index.html";    
    }
}

function chdir(role) {
    if (role == 'Admin') {
        
    } 
    else if(role == 'Doctor'){
        location.replace("../workspace/patient.html");
    }
    else if(role == 'Doctor_assist'){
        location.replace("../workspace/assistpatient.html");
    }
    else if(role == 'Nurse'){
        location.replace("../workspace/nurse.html");
    }
    else if(role == 'receptionist'){
        location.replace("../workspace/receptionist.html");
    }
    else if(role == 'laboratory'){
        location.replace("../workspace/laboratory.html");
    }
    else{
        location.replace("../index.html");    
    }
}

function passch() {
    try {
        let data = JSON.parse(localStorage.token);
        $.post("../bridge/cheker.php", { "action": "chek", "hash": data.password_hash }, function(response) {
            let geter = JSON.parse(response);
            if (geter.status === "success") {
                if (geter.role) {
                    // Handle the role-based logic here
                    let redirectUrl = get_dir(geter.role);
                    if(redirectUrl){

                    }else{
                        chdir(geter.role)
                    }
                }
            } else {
                console.log("Password check failed");
                // Additional error handling can be added here
            }
        });
    } catch (error) {
        console.error("Error parsing token or making request:", error);
    }
}

passch();
