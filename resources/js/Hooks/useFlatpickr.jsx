import { useEffect, useRef } from 'react';
import flatpickr from 'flatpickr';

export default function useFlatpickr(options = {}) {
    const ref = useRef(null);
    const instanceRef = useRef(null);

    useEffect(() => {
        if (!ref.current) return undefined;
        instanceRef.current = flatpickr(ref.current, {
            altInput: true,
            altFormat: 'd M Y',
            dateFormat: 'Y-m-d',
            ...options,
        });
        return () => {
            instanceRef.current?.destroy();
            instanceRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return ref;
}
