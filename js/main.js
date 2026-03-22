// LibraNova - Main JavaScript

// ── TOAST NOTIFICATIONS ──
function showToast(message, type = 'success') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'slideInRight 0.3s ease reverse';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// ── MODAL MANAGEMENT ──
function openModal(id) {
  document.getElementById(id)?.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  document.getElementById(id)?.classList.remove('active');
  document.body.style.overflow = '';
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// Close modal on Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active').forEach(m => {
      m.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
});

// ── NAVBAR TOGGLE ──
const navToggle = document.querySelector('.navbar-toggle');
const navMenu = document.querySelector('.navbar-nav');
if (navToggle && navMenu) {
  navToggle.addEventListener('click', () => navMenu.classList.toggle('open'));
}

// ── CONFIRM DELETE ──
function confirmDelete(message, formId) {
  if (confirm(message || 'Are you sure you want to delete this?')) {
    if (formId) {
      document.getElementById(formId)?.submit();
    }
    return true;
  }
  return false;
}

// ── SEARCH WITH DEBOUNCE ──
function debounce(fn, delay = 400) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Live search for book listing
const searchInput = document.getElementById('search-input');
if (searchInput) {
  searchInput.addEventListener('input', debounce(function() {
    document.getElementById('search-form')?.submit();
  }, 600));
}

// ── AJAX BOOK ACTIONS ──
async function issueBook(bookId, bookTitle) {
  if (!confirm(`Issue "${bookTitle}" to yourself?`)) return;

  try {
    const res = await fetch('actions/issue.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `book_id=${bookId}`
    });
    const data = await res.json();
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1500);
  } catch {
    showToast('Request failed. Please try again.', 'error');
  }
}

async function returnBook(issueId, bookTitle) {
  if (!confirm(`Return "${bookTitle}"?`)) return;

  try {
    const res = await fetch('actions/return.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `issue_id=${issueId}`
    });
    const data = await res.json();
    if (data.fine > 0) {
      showToast(`${data.message} Fine: $${data.fine.toFixed(2)}`, 'warning');
    } else {
      showToast(data.message, data.success ? 'success' : 'error');
    }
    if (data.success) setTimeout(() => location.reload(), 2000);
  } catch {
    showToast('Request failed. Please try again.', 'error');
  }
}

// ── ADMIN: POPULATE EDIT MODAL ──
function populateEditBook(book) {
  const modal = document.getElementById('edit-book-modal');
  if (!modal) return;

  modal.querySelector('[name="book_id"]').value = book.id;
  modal.querySelector('[name="title"]').value = book.title;
  modal.querySelector('[name="author"]').value = book.author;
  modal.querySelector('[name="category"]').value = book.category;
  modal.querySelector('[name="isbn"]').value = book.isbn || '';
  modal.querySelector('[name="publication_year"]').value = book.publication_year || '';
  modal.querySelector('[name="publisher"]').value = book.publisher || '';
  modal.querySelector('[name="description"]').value = book.description || '';
  modal.querySelector('[name="total_quantity"]').value = book.total_quantity;

  openModal('edit-book-modal');
}

// ── ADMIN: USER STATUS TOGGLE ──
async function toggleUserStatus(userId, currentStatus) {
  const action = currentStatus === 'active' ? 'suspend' : 'activate';
  if (!confirm(`${action.charAt(0).toUpperCase() + action.slice(1)} this user?`)) return;

  try {
    const res = await fetch('actions/user_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `user_id=${userId}&action=${action}`
    });
    const data = await res.json();
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1200);
  } catch {
    showToast('Request failed.', 'error');
  }
}

// ── DUE DATE HIGHLIGHTING ──
document.querySelectorAll('.due-date').forEach(el => {
  const date = new Date(el.dataset.date);
  const today = new Date();
  const diff = Math.ceil((date - today) / 86400000);
  
  if (diff < 0) {
    el.classList.add('text-danger');
    el.title = `${Math.abs(diff)} days overdue`;
  } else if (diff <= 3) {
    el.classList.add('text-warning');
    el.title = `Due in ${diff} day(s)`;
  }
});

// ── FILTER FORM AUTO-SUBMIT ──
document.querySelectorAll('.auto-submit').forEach(el => {
  el.addEventListener('change', () => el.closest('form')?.submit());
});

// ── CHARACTER COUNTER ──
document.querySelectorAll('textarea[maxlength]').forEach(el => {
  const counter = document.createElement('small');
  counter.style.cssText = 'display:block;text-align:right;color:#8a9ab5;margin-top:4px';
  el.parentNode.appendChild(counter);
  const update = () => counter.textContent = `${el.value.length}/${el.maxLength}`;
  el.addEventListener('input', update);
  update();
});

// ── FLASH MESSAGE AUTO DISMISS ──
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity 0.5s ease';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 5000);
});

// ── TABLE SEARCH ──
const tableSearch = document.getElementById('table-search');
if (tableSearch) {
  tableSearch.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.searchable-row').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

// ── PRINT ──
function printPage() { window.print(); }

// ── DATE FORMATTING ──
function formatDate(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Init: format all date elements
document.querySelectorAll('.format-date').forEach(el => {
  el.textContent = formatDate(el.dataset.date || el.textContent);
});

// ── PASSWORD STRENGTH INDICATOR ──
const pwInput = document.getElementById('password');
const pwStrength = document.getElementById('pw-strength');
if (pwInput && pwStrength) {
  pwInput.addEventListener('input', function() {
    const v = this.value;
    let strength = 0;
    if (v.length >= 8) strength++;
    if (/[A-Z]/.test(v)) strength++;
    if (/[0-9]/.test(v)) strength++;
    if (/[^A-Za-z0-9]/.test(v)) strength++;

    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#e74c3c', '#f39c12', '#3498db', '#27ae60'];
    pwStrength.textContent = labels[strength];
    pwStrength.style.color = colors[strength];
  });
}

// ── CONFIRM PASSWORD MATCH ──
const confirmPw = document.getElementById('confirm_password');
if (confirmPw && pwInput) {
  confirmPw.addEventListener('input', function() {
    if (this.value && this.value !== pwInput.value) {
      this.style.borderColor = '#e74c3c';
    } else {
      this.style.borderColor = '';
    }
  });
}
