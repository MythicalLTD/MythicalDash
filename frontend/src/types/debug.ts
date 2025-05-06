export interface NetworkLog {
    time: string;
    url: string;
    method?: string;
    status: number;
    statusText: string;
    requestBody?: unknown;
    responseBody?: unknown;
    headers?: Record<string, string>;
}

export interface ErrorLog {
    time: string;
    message: string;
    stack?: string;
}

export interface ConsoleLog {
    time: string;
    message: string;
}
