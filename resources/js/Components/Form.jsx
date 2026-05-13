import React, { useEffect, useRef } from 'react';
import TomSelect from 'tom-select';
import flatpickr from 'flatpickr';

export function TextField({ label, name, value, onChange, error, type = 'text', required, ...rest }) {
    return (
        <div className="mb-3">
            {label && <label className="form-label">{label}{required && <span className="text-danger">*</span>}</label>}
            <input
                type={type}
                name={name}
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
                className={`form-control ${error ? 'is-invalid' : ''}`}
                {...rest}
            />
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}

export function TextAreaField({ label, name, value, onChange, error, rows = 3, ...rest }) {
    return (
        <div className="mb-3">
            {label && <label className="form-label">{label}</label>}
            <textarea
                name={name}
                rows={rows}
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
                className={`form-control ${error ? 'is-invalid' : ''}`}
                {...rest}
            />
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}

export function CheckboxField({ label, name, value, onChange, error }) {
    return (
        <div className="mb-3 form-check">
            <input
                type="checkbox"
                id={name}
                className="form-check-input"
                checked={!!value}
                onChange={(e) => onChange(e.target.checked)}
            />
            <label htmlFor={name} className="form-check-label">{label}</label>
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}

export function SelectField({ label, name, value, onChange, options = [], error, placeholder, multiple = false, required }) {
    const ref = useRef(null);
    const tsRef = useRef(null);

    useEffect(() => {
        if (!ref.current) return undefined;
        tsRef.current = new TomSelect(ref.current, {
            create: false,
            allowEmptyOption: true,
            plugins: multiple ? ['remove_button'] : [],
            onChange: (val) => onChange(multiple ? (Array.isArray(val) ? val : []) : val),
        });
        return () => {
            try { tsRef.current?.destroy(); } catch (_e) { /* noop */ }
            tsRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [options.length, multiple]);

    useEffect(() => {
        if (!tsRef.current) return;
        const desired = multiple ? (Array.isArray(value) ? value.map(String) : []) : (value == null ? '' : String(value));
        const current = tsRef.current.getValue();
        if (JSON.stringify(current) !== JSON.stringify(desired)) {
            tsRef.current.setValue(desired, true);
        }
    }, [value, multiple]);

    return (
        <div className="mb-3">
            {label && <label className="form-label">{label}{required && <span className="text-danger">*</span>}</label>}
            <select
                ref={ref}
                name={name}
                multiple={multiple}
                className={`form-select ${error ? 'is-invalid' : ''}`}
                defaultValue={multiple ? (Array.isArray(value) ? value.map(String) : []) : (value == null ? '' : String(value))}
            >
                {placeholder && !multiple && <option value="">{placeholder}</option>}
                {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
            </select>
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}

export function DateField({ label, name, value, onChange, error, enableTime = false, required }) {
    const ref = useRef(null);
    const instance = useRef(null);

    useEffect(() => {
        if (!ref.current) return undefined;
        instance.current = flatpickr(ref.current, {
            enableTime,
            dateFormat: enableTime ? 'Y-m-d H:i' : 'Y-m-d',
            altInput: true,
            altFormat: enableTime ? 'd M Y H:i' : 'd M Y',
            defaultDate: value || undefined,
            onChange: (selected, dateStr) => onChange(dateStr),
        });
        return () => {
            instance.current?.destroy();
            instance.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [enableTime]);

    useEffect(() => {
        if (instance.current && value && instance.current.input.value !== value) {
            instance.current.setDate(value, false);
        }
    }, [value]);

    return (
        <div className="mb-3">
            {label && <label className="form-label">{label}{required && <span className="text-danger">*</span>}</label>}
            <input ref={ref} type="text" name={name} className={`form-control ${error ? 'is-invalid' : ''}`} />
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}

export function FileField({ label, name, onChange, error, accept = 'image/*' }) {
    return (
        <div className="mb-3">
            {label && <label className="form-label">{label}</label>}
            <input
                type="file"
                name={name}
                accept={accept}
                className={`form-control ${error ? 'is-invalid' : ''}`}
                onChange={(e) => onChange(e.target.files?.[0] ?? null)}
            />
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}
