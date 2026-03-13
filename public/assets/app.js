document.querySelectorAll('.batch-select').forEach((select) => {
  const info = select.closest('form')?.querySelector('.batch-info');
  const sync = () => {
    const option = select.options[select.selectedIndex];
    if (info && option) info.textContent = option.dataset.info || '';
  };
  select.addEventListener('change', sync);
  sync();
});
