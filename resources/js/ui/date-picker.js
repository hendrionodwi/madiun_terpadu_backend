const DATE_INPUT_SELECTOR = 'input[type="date"]:not([data-native-date-picker]), input[type="datetime-local"]:not([data-native-date-picker])';
const DATE_FORMATTER = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
const DATE_TIME_FORMATTER = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23',
});
const MONTH_FORMATTER = new Intl.DateTimeFormat('id-ID', {
    month: 'long',
    year: 'numeric',
});
const LONG_DATE_FORMATTER = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
const WEEKDAY_LABELS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

let activePicker = null;
let backdrop = null;
let panel = null;
let calendarGrid = null;
let monthLabel = null;
let panelHeading = null;
let timeFields = null;
let hourSelect = null;
let minuteSelect = null;
let clearButton = null;
let applyButton = null;
let viewDate = null;
let selectedDate = null;

const padNumber = (value) => String(value).padStart(2, '0');

const sameDate = (first, second) => (
    first instanceof Date
    && second instanceof Date
    && first.getFullYear() === second.getFullYear()
    && first.getMonth() === second.getMonth()
    && first.getDate() === second.getDate()
);

const parseLocalValue = (value) => {
    if (!value) {
        return null;
    }

    const [datePart, timePart = '00:00'] = value.split('T');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour = 0, minute = 0] = timePart.split(':').map(Number);

    if (![year, month, day, hour, minute].every(Number.isFinite)) {
        return null;
    }

    const date = new Date(year, month - 1, day, hour, minute);
    if (
        date.getFullYear() !== year
        || date.getMonth() !== month - 1
        || date.getDate() !== day
    ) {
        return null;
    }

    return date;
};

const formatInputValue = (date, includesTime) => {
    const dateValue = [
        date.getFullYear(),
        padNumber(date.getMonth() + 1),
        padNumber(date.getDate()),
    ].join('-');

    if (!includesTime) {
        return dateValue;
    }

    return `${dateValue}T${padNumber(date.getHours())}:${padNumber(date.getMinutes())}`;
};

const dateOnly = (date) => new Date(date.getFullYear(), date.getMonth(), date.getDate());

const dateWithinBounds = (date, input) => {
    const minDate = parseLocalValue(input.min);
    const maxDate = parseLocalValue(input.max);
    const value = dateOnly(date).getTime();

    if (minDate && value < dateOnly(minDate).getTime()) {
        return false;
    }

    if (maxDate && value > dateOnly(maxDate).getTime()) {
        return false;
    }

    return true;
};

const closestAllowedDate = (date, input) => {
    const minDate = parseLocalValue(input.min);
    const maxDate = parseLocalValue(input.max);

    if (minDate && dateOnly(date) < dateOnly(minDate)) {
        return new Date(minDate);
    }

    if (maxDate && dateOnly(date) > dateOnly(maxDate)) {
        return new Date(maxDate);
    }

    return date;
};

const createButton = (label, className, action) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = className;
    button.dataset.action = action;
    button.textContent = label;

    return button;
};

const ensurePanel = () => {
    if (panel?.isConnected) {
        return;
    }

    backdrop = document.createElement('div');
    backdrop.className = 'civic-date-picker-backdrop';
    backdrop.hidden = true;

    panel = document.createElement('section');
    panel.className = 'civic-date-picker-panel';
    panel.hidden = true;
    panel.tabIndex = -1;
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    panel.setAttribute('aria-labelledby', 'civic-date-picker-heading');
    panel.innerHTML = `
        <div class="civic-date-picker-panel-header">
            <div>
                <p class="civic-date-picker-eyebrow">Kalender</p>
                <h2 id="civic-date-picker-heading" class="civic-date-picker-heading">Pilih tanggal</h2>
            </div>
            <button type="button" class="civic-date-picker-close" data-action="cancel" aria-label="Tutup kalender">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </div>
        <div class="civic-date-picker-month-nav">
            <button type="button" class="civic-date-picker-nav-button" data-action="previous-month" aria-label="Bulan sebelumnya">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
            </button>
            <p class="civic-date-picker-month" aria-live="polite"></p>
            <button type="button" class="civic-date-picker-nav-button" data-action="next-month" aria-label="Bulan berikutnya">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
            </button>
        </div>
        <div class="civic-date-picker-weekdays" aria-hidden="true"></div>
        <div class="civic-date-picker-calendar" role="grid"></div>
        <div class="civic-date-picker-time" hidden>
            <div>
                <label for="civic-date-picker-hour">Jam</label>
                <select id="civic-date-picker-hour" aria-label="Jam"></select>
            </div>
            <span aria-hidden="true">:</span>
            <div>
                <label for="civic-date-picker-minute">Menit</label>
                <select id="civic-date-picker-minute" aria-label="Menit"></select>
            </div>
        </div>
        <div class="civic-date-picker-footer">
            <button type="button" class="civic-date-picker-text-button" data-action="clear">Hapus</button>
            <div>
                <button type="button" class="civic-date-picker-text-button" data-action="today">Hari ini</button>
                <button type="button" class="civic-date-picker-apply" data-action="apply">Terapkan</button>
            </div>
        </div>
    `;

    calendarGrid = panel.querySelector('.civic-date-picker-calendar');
    monthLabel = panel.querySelector('.civic-date-picker-month');
    panelHeading = panel.querySelector('.civic-date-picker-heading');
    timeFields = panel.querySelector('.civic-date-picker-time');
    hourSelect = panel.querySelector('#civic-date-picker-hour');
    minuteSelect = panel.querySelector('#civic-date-picker-minute');
    clearButton = panel.querySelector('[data-action="clear"]');
    applyButton = panel.querySelector('[data-action="apply"]');

    const weekdays = panel.querySelector('.civic-date-picker-weekdays');
    WEEKDAY_LABELS.forEach((label) => {
        const day = document.createElement('span');
        day.textContent = label;
        weekdays.append(day);
    });

    for (let hour = 0; hour < 24; hour += 1) {
        const option = document.createElement('option');
        option.value = padNumber(hour);
        option.textContent = padNumber(hour);
        hourSelect.append(option);
    }

    for (let minute = 0; minute < 60; minute += 1) {
        const option = document.createElement('option');
        option.value = padNumber(minute);
        option.textContent = padNumber(minute);
        minuteSelect.append(option);
    }

    backdrop.addEventListener('click', () => closePicker());
    panel.addEventListener('click', handlePanelClick);
    panel.addEventListener('keydown', handlePanelKeydown);
    hourSelect.addEventListener('change', updateSelectedTime);
    minuteSelect.addEventListener('change', updateSelectedTime);

    document.body.append(backdrop, panel);
};

const fieldLabel = (input) => {
    if (input.id) {
        const label = document.querySelector(`label[for="${CSS.escape(input.id)}"]`);
        if (label?.textContent.trim()) {
            return label.textContent.trim().replace(/\s+/g, ' ');
        }
    }

    return input.closest('label')?.textContent.trim().replace(/\s+/g, ' ')
        || input.title
        || (input.type === 'datetime-local' ? 'Tanggal dan waktu' : 'Tanggal');
};

const syncTrigger = (picker) => {
    const value = parseLocalValue(picker.input.value);
    const hasValue = value instanceof Date;
    const formatter = picker.includesTime ? DATE_TIME_FORMATTER : DATE_FORMATTER;

    picker.valueText.textContent = hasValue ? formatter.format(value) : picker.placeholder;
    picker.trigger.dataset.empty = hasValue ? 'false' : 'true';
    picker.trigger.disabled = picker.input.disabled || picker.input.readOnly;
    picker.trigger.setAttribute('aria-label', `${fieldLabel(picker.input)}: ${picker.valueText.textContent}`);
};

const positionPanel = () => {
    if (!activePicker || panel.hidden || window.matchMedia('(max-width: 40rem)').matches) {
        panel.style.removeProperty('top');
        panel.style.removeProperty('left');
        return;
    }

    const triggerBounds = activePicker.trigger.getBoundingClientRect();
    const panelBounds = panel.getBoundingClientRect();
    const gap = 10;
    const edge = 12;
    const left = Math.min(
        Math.max(edge, triggerBounds.left),
        window.innerWidth - panelBounds.width - edge,
    );
    const below = triggerBounds.bottom + gap;
    const above = triggerBounds.top - panelBounds.height - gap;
    const top = below + panelBounds.height <= window.innerHeight - edge
        ? below
        : Math.max(edge, above);

    panel.style.left = `${left}px`;
    panel.style.top = `${top}px`;
};

const renderCalendar = () => {
    if (!activePicker || !viewDate) {
        return;
    }

    monthLabel.textContent = MONTH_FORMATTER.format(viewDate);
    calendarGrid.replaceChildren();

    const year = viewDate.getFullYear();
    const month = viewDate.getMonth();
    const firstDayOffset = (new Date(year, month, 1).getDay() + 6) % 7;
    const today = new Date();

    for (let index = 0; index < 42; index += 1) {
        const day = index - firstDayOffset + 1;
        const cellDate = new Date(year, month, day);
        const button = document.createElement('button');
        const isOutsideMonth = cellDate.getMonth() !== month;
        const isAllowed = dateWithinBounds(cellDate, activePicker.input);

        button.type = 'button';
        button.className = 'civic-date-picker-day';
        button.dataset.date = formatInputValue(cellDate, false);
        button.textContent = String(cellDate.getDate());
        button.setAttribute('role', 'gridcell');
        button.setAttribute('aria-label', LONG_DATE_FORMATTER.format(cellDate));

        if (isOutsideMonth) {
            button.classList.add('is-outside');
        }
        if (sameDate(cellDate, today)) {
            button.classList.add('is-today');
        }
        if (sameDate(cellDate, selectedDate)) {
            button.classList.add('is-selected');
            button.setAttribute('aria-selected', 'true');
        }
        if (!isAllowed) {
            button.disabled = true;
        }

        calendarGrid.append(button);
    }

    if (activePicker.includesTime && selectedDate) {
        hourSelect.value = padNumber(selectedDate.getHours());
        minuteSelect.value = padNumber(selectedDate.getMinutes());
    }
};

const updateSelectedTime = () => {
    if (!selectedDate) {
        return;
    }

    selectedDate.setHours(Number(hourSelect.value), Number(minuteSelect.value), 0, 0);
};

const applyValue = (value) => {
    if (!activePicker) {
        return;
    }

    activePicker.input.value = value;
    activePicker.input.dispatchEvent(new Event('input', { bubbles: true }));
    activePicker.input.dispatchEvent(new Event('change', { bubbles: true }));
    syncTrigger(activePicker);
    closePicker(true);
};

const handlePanelClick = (event) => {
    const button = event.target.closest('button');
    if (!button || !activePicker) {
        return;
    }

    const dayValue = button.dataset.date;
    if (dayValue) {
        const date = parseLocalValue(dayValue);
        if (!date) {
            return;
        }

        const hour = selectedDate?.getHours() ?? 0;
        const minute = selectedDate?.getMinutes() ?? 0;
        selectedDate = date;
        selectedDate.setHours(hour, minute, 0, 0);
        viewDate = new Date(date.getFullYear(), date.getMonth(), 1);
        renderCalendar();
        calendarGrid.querySelector(`[data-date="${dayValue}"]`)?.focus();
        return;
    }

    switch (button.dataset.action) {
        case 'previous-month':
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
            renderCalendar();
            break;
        case 'next-month':
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
            renderCalendar();
            break;
        case 'today': {
            const today = closestAllowedDate(new Date(), activePicker.input);
            const hour = selectedDate?.getHours() ?? today.getHours();
            const minute = selectedDate?.getMinutes() ?? today.getMinutes();
            selectedDate = new Date(today);
            selectedDate.setHours(hour, minute, 0, 0);
            viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
            renderCalendar();
            break;
        }
        case 'clear':
            applyValue('');
            break;
        case 'apply':
            if (selectedDate) {
                updateSelectedTime();
                applyValue(formatInputValue(selectedDate, activePicker.includesTime));
            }
            break;
        case 'cancel':
            closePicker(true);
            break;
        default:
            break;
    }
};

const focusDayByOffset = (currentButton, offset) => {
    const date = parseLocalValue(currentButton.dataset.date);
    if (!date || !activePicker) {
        return;
    }

    date.setDate(date.getDate() + offset);
    if (!dateWithinBounds(date, activePicker.input)) {
        return;
    }

    selectedDate = new Date(date);
    viewDate = new Date(date.getFullYear(), date.getMonth(), 1);
    renderCalendar();
    calendarGrid.querySelector(`[data-date="${formatInputValue(date, false)}"]`)?.focus();
};

const handlePanelKeydown = (event) => {
    if (event.key === 'Escape') {
        event.preventDefault();
        closePicker(true);
        return;
    }

    if (event.key === 'Tab') {
        const focusable = Array.from(panel.querySelectorAll('button:not(:disabled):not([hidden]), select:not(:disabled):not([hidden])'))
            .filter((element) => element.offsetParent !== null);
        const first = focusable[0];
        const last = focusable.at(-1);

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }

        return;
    }

    const dayButton = event.target.closest('.civic-date-picker-day');
    if (!dayButton) {
        return;
    }

    const dayOffsets = {
        ArrowLeft: -1,
        ArrowRight: 1,
        ArrowUp: -7,
        ArrowDown: 7,
        Home: -((new Date(`${dayButton.dataset.date}T00:00`).getDay() + 6) % 7),
        End: 6 - ((new Date(`${dayButton.dataset.date}T00:00`).getDay() + 6) % 7),
    };

    if (Object.hasOwn(dayOffsets, event.key)) {
        event.preventDefault();
        focusDayByOffset(dayButton, dayOffsets[event.key]);
        return;
    }

    if (event.key === 'PageUp' || event.key === 'PageDown') {
        event.preventDefault();
        viewDate = new Date(
            viewDate.getFullYear(),
            viewDate.getMonth() + (event.key === 'PageUp' ? -1 : 1),
            1,
        );
        renderCalendar();
        calendarGrid.querySelector('.is-selected:not(:disabled), .civic-date-picker-day:not(:disabled)')?.focus();
    }
};

const closePicker = (restoreFocus = false) => {
    if (!activePicker || !panel) {
        return;
    }

    const picker = activePicker;
    panel.hidden = true;
    backdrop.hidden = true;
    picker.trigger.setAttribute('aria-expanded', 'false');
    document.documentElement.classList.remove('civic-date-picker-open');
    window.removeEventListener('resize', positionPanel);
    window.removeEventListener('scroll', positionPanel, true);
    activePicker = null;

    if (restoreFocus) {
        picker.trigger.focus({ preventScroll: true });
    }
};

const openPicker = (picker) => {
    if (picker.trigger.disabled) {
        return;
    }

    ensurePanel();
    if (activePicker && activePicker !== picker) {
        closePicker();
    }

    activePicker = picker;
    const currentValue = parseLocalValue(picker.input.value);
    const initialDate = closestAllowedDate(currentValue || new Date(), picker.input);
    selectedDate = new Date(initialDate);
    viewDate = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);

    panelHeading.textContent = fieldLabel(picker.input);
    timeFields.hidden = !picker.includesTime;
    clearButton.hidden = picker.input.required;
    applyButton.textContent = picker.includesTime ? 'Pilih tanggal & waktu' : 'Pilih tanggal';
    picker.trigger.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    backdrop.hidden = false;
    document.documentElement.classList.add('civic-date-picker-open');
    renderCalendar();
    positionPanel();

    window.addEventListener('resize', positionPanel);
    window.addEventListener('scroll', positionPanel, true);

    requestAnimationFrame(() => {
        const focusTarget = calendarGrid.querySelector('.is-selected:not(:disabled)')
            || calendarGrid.querySelector('.civic-date-picker-day:not(:disabled)');
        focusTarget?.focus({ preventScroll: true });
    });
};

const enhanceInput = (input) => {
    if (input.dataset.civicDatePickerReady === 'true') {
        input._civicDatePicker?.sync();
        return;
    }

    const wrapper = document.createElement('div');
    const trigger = document.createElement('button');
    const icon = document.createElement('span');
    const valueText = document.createElement('span');
    const chevron = document.createElement('span');
    const includesTime = input.type === 'datetime-local';
    const placeholder = input.dataset.placeholder
        || input.placeholder
        || input.title
        || (includesTime ? 'Pilih tanggal dan waktu' : 'Pilih tanggal');

    wrapper.className = 'civic-date-picker';
    if (input.classList.contains('mt-1')) {
        wrapper.classList.add('mt-1');
    }

    trigger.type = 'button';
    trigger.className = 'civic-date-picker-trigger';
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', 'false');

    icon.className = 'civic-date-picker-trigger-icon';
    icon.innerHTML = `
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 3v3m10-3v3M4.5 9.5h15M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
        </svg>
    `;
    valueText.className = 'civic-date-picker-trigger-value';
    chevron.className = 'civic-date-picker-trigger-chevron';
    chevron.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 10 4 4 4-4" /></svg>';
    trigger.append(icon, valueText, chevron);

    input.before(wrapper);
    wrapper.append(input, trigger);
    input.dataset.civicDatePickerReady = 'true';
    input.dataset.civicDateNative = 'true';
    input.tabIndex = -1;
    input.setAttribute('aria-hidden', 'true');

    const picker = {
        input,
        wrapper,
        trigger,
        valueText,
        includesTime,
        placeholder,
        sync: () => syncTrigger(picker),
    };
    input._civicDatePicker = picker;

    trigger.addEventListener('click', () => openPicker(picker));
    input.addEventListener('change', picker.sync);
    input.addEventListener('input', picker.sync);
    input.addEventListener('focus', () => {
        input.blur();
        openPicker(picker);
    });
    input.addEventListener('invalid', () => {
        openPicker(picker);
        trigger.focus({ preventScroll: true });
    });
    input.form?.addEventListener('reset', () => requestAnimationFrame(picker.sync));

    picker.sync();
};

export const initDatePickers = (root = document) => {
    root.querySelectorAll(DATE_INPUT_SELECTOR).forEach(enhanceInput);
    document.querySelectorAll('[data-civic-date-picker-ready="true"]').forEach((input) => {
        input._civicDatePicker?.sync();
    });
};
