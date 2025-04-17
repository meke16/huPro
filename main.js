    // Delete confirmation
    function confirmDelete(id) {
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        var deleteBtn = document.getElementById('confirmDeleteBtn');
        
        deleteBtn.href = 'display.php?deleteid=' + id;
        deleteModal.show();
    }
    // View student profile
    function viewProfile(id) {
        $.get('display.php', {
            get_student: 1,
            id: id
        }, function(data) {
            try {
                var student = JSON.parse(data);
                if (student.error) {
                    alert(student.error);
                    return;
                }
                
                var photo = student.photo || 'assets/default-profile.jpg';
                
                var profileHtml = `
                    <div class="profile-header text-center">
                        <img src="${photo}" class="profile-img mb-2" alt="Student Photo">
                        <h3>${student.name}</h3>
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">Gender:</div>
                            <div class="col-md-8 detail-info">${student.sex}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">ID Number:</div>
                            <div class="col-md-8 detail-info">${student.idNumber}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">Department:</div>
                            <div class="col-md-8 detail-info">${student.department}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">Batch:</div>
                            <div class="col-md-8 detail-info">${student.year}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">Campus:</div>
                            <div class="col-md-8 detail-info">${student.campus}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">PC Serial Number:</div>
                            <div class="col-md-8 detail-info">${student.pcSerialNumber}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">PC Model:</div>
                            <div class="col-md-8 detail-info">${student.pcModel || 'Not specified'}</div>
                        </div>
                        <div class="row detail-row">
                            <div class="col-md-4 detail-label">Contact:</div>
                            <div class="col-md-8 detail-info">${student.contact || 'Not provided'}</div>
                        </div>
                    </div>
                `;
                
                $('#profileContent').html(profileHtml);
                $('#profileOverlay').show();
            } catch (e) {
                console.error('Error parsing student data:', e);
                alert('Error loading student profile');
            }
        }).fail(function() {
            alert('Failed to load student profile');
        });
    }

    // Close profile view
    function closeProfile() {
        $('#profileOverlay').hide();
    }

    // Form validation
    (function() {
        'use strict'
        
        var forms = document.querySelectorAll('.needs-validation')
        
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
    // Array of departments in Haramaya University (Example)
    const departments = [
            // College of Agriculture and Environmental Sciences (CAES)
        "Animal and Range Science",
        "Natural Resources and Environmental Science",
        "Plant Sciences",
        "Agricultural Economics and Agribusiness",
        "Rural Development and Agricultural Extension",

        //College of Business and Economics (CBE)
        "Accounting",
        "Cooperatives",
        "Management",
        "Economics",
        "Public Administration and Development Management",

        //College of Computing and Informatics
        "Computer Science",
        "Information Science",
        "Information Technology",
        "Software Engineering",
        "Statistics",

        // College of Education and Behavioral Sciences
        "Pedagogy",
        "Special Needs",
        "Educational Planning and Management",
        "English Language Improvement Centre",

        // College of Health and Medical Sciences
        "Medicine",
        "Pharmacy",
        "Nursing and Midwifery",
        "Public Health",
        "Environmental Health Sciences",
        "Medical Laboratory Science",

        //College of Law
        "Law",

        // College of Natural and Computational Sciences
        "Biology",
        "Chemistry",
        "Mathematics",
        "Physics",

        //College of Social Sciences and Humanities
        "Afan Oromo, Literature and Communication",
        "Gender and Development Studies",
        "Foreign Languages and Journalism",
        "History and Heritage Management",
        "Geography and Environmental Studies",
        "Sociology",

        // College of Veterinary Medicine
        "Veterinary Medicine",
        "Veterinary Laboratory Technology",

        // Haramaya Institute of Technology
        "Agricultural Engineering",
        "Water Resources and Irrigation Engineering",
        "Civil Engineering",
        "Electrical and Computer Engineering",
        "Mechanical Engineering",
        "Chemical Engineering",
        "Food Science and Post-Harvest Technology",
        "Food Technology and Process Engineering",

        // Sport Sciences Academy
        "Sport Sciences",

        // College of Agro-Industry and Land Resources
        "Land Administration",
        "Dairy and Meat Technology",
        "Forest Resource Management",
        "Soil Resources and Watershed Management"
    ];

    // Get the select element
    const departmentSelect = document.getElementById("department");

    // Populate the select dropdown with department options
    departments.forEach(function(department) {
        const option = document.createElement("option");
        option.value = department;
        option.textContent = department;
        departmentSelect.appendChild(option);
    });