declare module 'web-vitals' {
    type MetricType = 'CLS' | 'FID' | 'FCP' | 'LCP' | 'TTFB';
    type MetricRating = 'good' | 'needs-improvement' | 'poor';
    type MetricValue = number;

    interface Metric {
        name: MetricType;
        value: MetricValue;
        rating: MetricRating;
        delta: MetricValue;
        entries: PerformanceEntry[];
    }

    type ReportHandler = (metric: Metric) => void;

    export function onCLS(onReport: ReportHandler): void;
    export function onFID(onReport: ReportHandler): void;
    export function onFCP(onReport: ReportHandler): void;
    export function onLCP(onReport: ReportHandler): void;
    export function onTTFB(onReport: ReportHandler): void;
}
