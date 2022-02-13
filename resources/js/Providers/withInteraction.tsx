import React, { ComponentType } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

export interface Options {
    onClick?: VoidFunction;
    modal?: string;
    to?: string;
}

export default function withInteraction<T extends Options>(Component: ComponentType<T>) {
    return (options: any): JSX.Element => {
        const navigate = useNavigate();
        const location = useLocation();
        const { onClick, modal, to } = options;

        function action(): VoidFunction | undefined {
            if (onClick) {
                return onClick;
            } else if (modal) {
                return (): void => navigate(modal, { state: { from: location } });
            } else if (to) {
                return (): void => navigate(to);
            }
        }
        const newProps = { ...options, action: action() } as T;

        return <Component {...newProps} />;
    };
}
