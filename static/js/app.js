document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab');
  const roleInput = document.getElementById('role_choice');
  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      tabs.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const role = btn.dataset.role || 'user';
      roleInput.value = role;
    });
  });
});
