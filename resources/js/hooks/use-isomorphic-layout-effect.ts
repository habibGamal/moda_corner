import { useEffect, useLayoutEffect } from 'react';

// Hook that uses useLayoutEffect on client and useEffect on server
// This prevents SSR warnings while maintaining proper behavior
export const useIsomorphicLayoutEffect = typeof window !== 'undefined' ? useLayoutEffect : useEffect;

export default useIsomorphicLayoutEffect;
