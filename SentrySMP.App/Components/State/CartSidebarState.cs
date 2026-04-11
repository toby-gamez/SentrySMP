namespace SentrySMP.App.Components.State;

public class CartSidebarState
{
    public bool IsOpen { get; private set; }
    public event Action? OnChange;

    public void Open()
    {
        IsOpen = true;
        OnChange?.Invoke();
    }

    public void Close()
    {
        IsOpen = false;
        OnChange?.Invoke();
    }
}
