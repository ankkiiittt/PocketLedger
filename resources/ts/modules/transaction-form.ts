export interface CategoryItem {
  id: number | string;
  name: string;
  type: 'expense' | 'income';
}

/**
 * Filter categories by type. Pure function for testability.
 */
export function filterCategoriesByType<T extends { type: string }>(
  categories: T[],
  type: string
): T[] {
  return categories.filter((cat) => cat.type === type);
}

/**
 * Filter HTML select option elements based on data-type attribute.
 */
export function filterSelectOptionsByType(
  selectEl: HTMLSelectElement,
  selectedType: string
): void {
  const options = Array.from(selectEl.options);
  let firstValidOption: HTMLOptionElement | null = null;
  let hasValidSelection = false;

  options.forEach((opt) => {
    const optType = opt.dataset.type;
    if (!optType) {
      return;
    }

    if (optType === selectedType) {
      opt.hidden = false;
      opt.disabled = false;
      if (!firstValidOption) {
        firstValidOption = opt;
      }
      if (opt.selected) {
        hasValidSelection = true;
      }
    } else {
      opt.hidden = true;
      opt.disabled = true;
      if (opt.selected) {
        opt.selected = false;
      }
    }
  });

  if (!hasValidSelection && firstValidOption) {
    (firstValidOption as HTMLOptionElement).selected = true;
  }
}

/**
 * Initialize transaction form type-toggle filtering.
 */
export function initTransactionForm(): void {
  const form = document.querySelector<HTMLFormElement>('[data-transaction-form]');
  if (!form) {
    return;
  }

  const categorySelect = form.querySelector<HTMLSelectElement>('[data-category-select]');
  const typeRadios = form.querySelectorAll<HTMLInputElement>('input[name="type_toggle"]');

  if (!categorySelect || typeRadios.length === 0) {
    return;
  }

  const handleTypeChange = (selectedType: string) => {
    filterSelectOptionsByType(categorySelect, selectedType);
  };

  typeRadios.forEach((radio) => {
    radio.addEventListener('change', () => {
      if (radio.checked) {
        handleTypeChange(radio.value);
      }
    });

    if (radio.checked) {
      handleTypeChange(radio.value);
    }
  });
}
