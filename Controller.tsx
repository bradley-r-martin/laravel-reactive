import { NavigateFunction, useNavigate, useParams } from 'react-router-dom';
import React, { createContext } from 'react';

import Subscriptions from './Subscriptions';
import _ from 'lodash';
import axios from 'axios';

export interface ControllerProperties {
    state: any;
    dispatching: (action: string) => boolean;
    dispatch: (action: string) => undefined;
}
export interface ControllerParameters {
    controller: string;
    parameters?: string[];
}

export interface bindProps {
    defer?: boolean;
    name: string;
}

export interface ControllerContext {
    state: object;
    errors: { [key: string]: string[] };
    bind?: ({ defer }: bindProps) => object;
    value: (name: string) => string | object | boolean;
    setValue: (name: string, value: string | object | boolean, defer?: boolean) => void;
}

export interface ControllerState {
    subscriptions: string[];
    version: string;
    state: object;
    errors: { [key: string]: string[] };
    dispatching: null | string;
    _subscribed: string[];
}

export const ControllerContext = createContext<ControllerContext>({
    errors: {},
    state: {},
    value: (): object => ({}),
    setValue: (): object => ({}),
    bind: (): object => ({}),
});

export default function Controller({ controller }: ControllerParameters, Component: any): React.ComponentType {
    class Controller extends React.PureComponent<{ navigate: NavigateFunction }, ControllerState> {
        constructor(props: any) {
            super(props);
            this.state = {
                version: '',
                state: {},
                errors: {},
                subscriptions: [],
                dispatching: null,
                _subscribed: [],
            };
            this.request = this.request.bind(this);
            this.onMount = this.onMount.bind(this);
            this.bind = this.bind.bind(this);
            this.dispatch = this.dispatch.bind(this);
            this.dispatching = this.dispatching.bind(this);
            this.subscribe = this.subscribe.bind(this);

            this.handleValidationResponse = this.handleValidationResponse.bind(this);
            this.value = this.value.bind(this);
            this.setValue = this.setValue.bind(this);
        }

        componentDidMount(): void {
            this.onMount();
        }
        componentWillUnmount(): void {
            const { _subscribed } = this.state;
            _subscribed.map((event): void => this.unsubscribeToEvent(event));
        }

        UNSAFE_componentWillReceiveProps(): void {
            this.onMount();
        }

        request(action: string): void {
            const payload = {
                action,
                controller,
                state: this.state.state,
                props: this.props,
            };
            axios
                .post('/reactive', payload)
                .then((response) => {
                    if (new URL(response.request.responseURL).pathname !== '/reactive') {
                        this.props.navigate(new URL(response.request.responseURL).pathname);
                        return;
                    }
                    if (response.status === 200) {
                        this.setState(
                            {
                                state: response.data.state,
                                version: response.data.version,
                                subscriptions: response.data.subscriptions ?? [],
                                dispatching: null,
                            },
                            (): void => this.subscribe(),
                        );
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.handleValidationResponse(error.response.data);
                    } else {
                        this.handleHtmlResponse(error.response.data);
                        this.setState({ dispatching: null });
                    }
                });
        }

        onMount(): void {
            this.setState({ dispatching: 'onMount' }, () => this.request('onMount'));
        }

        dispatch(action: string): void {
            this.setState({ dispatching: action }, () => this.request(action));
        }
        dispatching(action: string): boolean {
            return this.state.dispatching === action;
        }

        unsubscribeToEvent(event: string): void {
            console.log(`%c unsubscribing to ${event} on ${controller}`, 'color: #eb4034');
            Subscriptions.unsubscribe(event, this.onMount);
        }
        subscribeToEvent(event: string): void {
            console.log(`%c subscribing to ${event} on ${controller}`, 'color: #25b045');
            Subscriptions.subscribe(event, this.onMount);
        }

        subscribe(): void {
            const { subscriptions, _subscribed } = this.state;

            const removableSubscriptions = _subscribed.filter((x) => !subscriptions.includes(x));
            const AddableSubscriptions = subscriptions.filter((x) => !_subscribed.includes(x));

            removableSubscriptions.map((event): void => this.unsubscribeToEvent(event));

            AddableSubscriptions.map((event): void => this.subscribeToEvent(event));

            this.setState({
                _subscribed: subscriptions,
            });
        }

        bind({ defer, name }: bindProps): object {
            return {
                value: _.get(this.state.state, name) ?? '',
                onChange: ({ target: { value, name, type } }: any): void => {
                    const initial = _.get(this.state.state, name);
                    this.setState(
                        { state: { ..._.set(this.state.state, name, type === 'checkbox' ? !initial : value) } },
                        (): void => {
                            if (!defer) {
                                this.dispatch(`updating:${name}`);
                            }
                        },
                    );
                },
            };
        }

        handleHtmlResponse(html: string): void {
            alert(html);
        }
        handleValidationResponse(response: any): void {
            this.setState({ errors: response.errors, dispatching: null });
        }

        value(name: string): string | object | boolean {
            return _.get(this.state.state, name) ?? '';
        }
        setValue(name: string, value: string | object | boolean, defer = true): void {
            this.setState({ state: { ..._.set(this.state.state, name, value) } }, (): void => {
                if (!defer) {
                    this.dispatch(`updating:${name}`);
                }
            });
        }

        render(): JSX.Element {
            return (
                <ControllerContext.Provider
                    value={{
                        errors: this.state.errors,
                        state: this.state.state,
                        bind: this.bind,
                        value: this.value,
                        setValue: this.setValue,
                    }}
                >
                    <Component
                        state={this.state.state}
                        bind={this.bind}
                        dispatch={this.dispatch}
                        dispatching={this.dispatching}
                    />
                </ControllerContext.Provider>
            );
        }
    }

    return (props: any) => {
        const navigate = useNavigate();
        const parameters = useParams();
        return <Controller navigate={navigate} {...parameters} {...props} />;
    };
}
