function setRole(role, element) {
    const roleInput = document.getElementById('selected_role');
    if (roleInput) {
        roleInput.value = role;
    }
    
    // Toggle active tab visual state
    const buttons = document.querySelectorAll('.role-selector button');
    buttons.forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
}