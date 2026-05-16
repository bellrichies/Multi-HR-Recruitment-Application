import { useCallback, useEffect, useState } from 'react';

export function useApi(loader, dependencies = []) {
  const [data, setData] = useState(null);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await loader();
      setData(response.data);
      setMeta(response.meta || null);
    } catch (err) {
      setError(err);
    } finally {
      setLoading(false);
    }
  }, dependencies);

  useEffect(() => {
    refresh();
  }, [refresh]);

  return { data, meta, loading, error, refresh };
}
