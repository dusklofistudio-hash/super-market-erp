import { useEffect, useRef } from 'react';
import TomSelect from 'tom-select';

export default function useTomSelect(options = {}) {
    const ref = useRef(null);
    const instanceRef = useRef(null);

    useEffect(() => {
        if (!ref.current) return undefined;
        instanceRef.current = new TomSelect(ref.current, {
            create: false,
            allowEmptyOption: true,
            ...options,
        });
        return () => {
            try { instanceRef.current?.destroy(); } catch (_e) { /* noop */ }
            instanceRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return ref;
}
