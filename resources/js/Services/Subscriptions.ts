declare global {
    interface Window {
        Echo: any;
    }
}
class SubscriptionsService {
    public subscriptions;
    public subscribed;
    constructor() {
        this.subscriptions = <any>{};
        this.subscribed = <string[]>[];
    }
    subscribe(event: string, callback: VoidFunction): void {
        const channels = <VoidFunction[]>(this.subscriptions[event] ?? []);
        this.subscriptions[event] = [...channels, callback];
        this.sync();
    }
    unsubscribe(event: string, callback: VoidFunction): void {
        const channels = <VoidFunction[]>(this.subscriptions[event] ?? []);

        for (let i = channels.length - 1; i >= 0; i--) {
            if (channels[i] === callback) {
                channels.splice(i, 1);
            }
        }
        this.subscriptions[event] = channels;

        this.subscriptions = Object.entries(this.subscriptions).reduce((a: any, i: any) => {
            return i[1].length > 0 ? { ...a, [i[0]]: i[1] } : a;
        }, {});
        this.sync();
    }
    sync(): void {
        const removableSubscriptions = this.subscribed.filter((x) => !Object.keys(this.subscriptions).includes(x));
        const AddableSubscriptions = Object.keys(this.subscriptions).filter((x) => !this.subscribed.includes(x));

        removableSubscriptions.map((channel) => {
            console.log(`%c unsubscribing to ${channel}`, 'color: #eb4034');
            window.Echo.leave(channel);
        });

        AddableSubscriptions.map((channel) => {
            console.log(`%c subscribing to ${channel}`, 'color: #25b045');
            window.Echo.channel(channel)
                .listen('.created', () => {
                    this.subscriptions[channel].map((fn: VoidFunction) => fn());
                })
                .listen('.updated', () => {
                    this.subscriptions[channel].map((fn: VoidFunction) => fn());
                })
                .listen('.deleted', () => {
                    this.subscriptions[channel].map((fn: VoidFunction) => fn());
                });
        });

        this.subscribed = Object.keys(this.subscriptions);
    }
}
const Subscriptions = new SubscriptionsService();

export default Subscriptions;
