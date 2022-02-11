import { Outlet } from 'react-router-dom';
import React from 'react';

export default function Layout(): JSX.Element {
    return (
        <div className="flex flex-1 overflow-hidden h-screen">
            <Outlet />
        </div>
    );
}
