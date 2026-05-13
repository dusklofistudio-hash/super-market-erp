import React from 'react';

export default function PageHeader({ title, subtitle, actions = null }) {
    return (
        <div className="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h4 className="mb-0">{title}</h4>
                {subtitle && <div className="text-muted small">{subtitle}</div>}
            </div>
            <div className="d-flex gap-2">{actions}</div>
        </div>
    );
}
