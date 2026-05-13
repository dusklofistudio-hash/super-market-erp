import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';

const I18nContext = createContext(null);

export function I18nProvider({ children, initial = {}, initialLocale = 'en', availableLocales = [] }) {
    const [locale, setLocale] = useState(initialLocale);
    const [translations, setTranslations] = useState(initial || {});

    const t = useCallback(
        (key, replacements = {}) => {
            const bag = translations?.[locale] || {};
            let value = bag[key] ?? key;
            if (replacements && typeof value === 'string') {
                for (const [k, v] of Object.entries(replacements)) {
                    value = value.replaceAll(`:${k}`, String(v));
                }
            }
            return value;
        },
        [translations, locale]
    );

    const changeLocale = useCallback(async (next) => {
        if (next === locale) return;
        try {
            const res = await window.axios.post('/admin/locale', { locale: next });
            const data = res.data || {};
            if (data.translations) {
                setTranslations((prev) => ({ ...prev, [next]: data.translations }));
            }
            setLocale(next);
            document.documentElement.setAttribute('lang', next);
            window.dispatchEvent(new CustomEvent('smk:locale-changed', { detail: { locale: next } }));
        } catch (e) {
            console.error('Failed to switch locale', e);
        }
    }, [locale]);

    const value = useMemo(() => ({ locale, t, changeLocale, availableLocales }),
        [locale, t, changeLocale, availableLocales]);

    return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n() {
    const ctx = useContext(I18nContext);
    if (!ctx) throw new Error('useI18n must be used inside I18nProvider');
    return ctx;
}

export function useT() {
    return useI18n().t;
}
