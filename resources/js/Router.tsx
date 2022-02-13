import { BrowserRouter, Route, Routes, useLocation } from 'react-router-dom';

import Base from './Layouts/Base';
import E404 from './Controllers/Exceptions/E404';
import { LocationState } from '../../LocationState';
import React from 'react';

const Router = (): JSX.Element => {
    const location = useLocation();
    const { from } = location.state as LocationState;

    return (
        <>
            <Routes location={from || location}>
                <Route path="/" element={<Base />}>
                    <Route path="*" element={<E404 />} />
                </Route>
            </Routes>
            {/* Modals */}
            <Routes></Routes>
        </>
    );
};
export default (): JSX.Element => {
    return (
        <BrowserRouter>
            <Router />
        </BrowserRouter>
    );
};
