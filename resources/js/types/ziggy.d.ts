declare module 'ziggy-js' {
    export type RouteName = string;
    export interface Config {
        [key: string]: any;
    }
    export function route(name?: any, params?: any, absolute?: boolean, config?: Config): any;
    export default route;
}
