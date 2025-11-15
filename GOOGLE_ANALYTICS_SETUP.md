# Google Analytics Setup Instructions

## Problem Fixed
The `NS_ERROR_DOM_BAD_URI` error was caused by using an invalid Google Analytics tracking ID (`G-SGG2CLM06D`). This has been fixed by:

1. Adding validation for tracking ID format
2. Adding proper error handling
3. Allowing GA to be completely disabled
4. Adding debug logging

## How to Set Up Google Analytics

### Step 1: Get Your Tracking ID
1. Go to [Google Analytics](https://analytics.google.com/)
2. Create a new GA4 property or use an existing one
3. Go to Admin → Data Streams → Your Stream
4. Copy the "Measurement ID" (format: `G-XXXXXXXXXX`)

### Step 2: Update the Code
In `/SentrySMP.App/Components/CookieBanner.razor`, find this line:
```csharp
private const string GtagId = ""; // "G-XXXXXXXXXX" - Replace with your real GA4 tracking ID
```

Replace it with your actual tracking ID:
```csharp
private const string GtagId = "G-YOUR-REAL-ID-HERE";
```

### Step 3: Test
1. Build and run your application
2. Open browser Developer Tools (F12)
3. Go to Console tab
4. Accept cookies on your website
5. Look for `[GA Debug]` messages in console
6. Check Network tab for requests to `google-analytics.com`

## Alternative: Move to Configuration

For production, consider moving the tracking ID to `appsettings.json`:

```json
{
  "GoogleAnalytics": {
    "TrackingId": "G-YOUR-REAL-ID-HERE"
  }
}
```

Then inject it via configuration instead of hardcoding.

## Disable Google Analytics Completely

To disable Google Analytics:
1. Set `GtagId = ""` (empty string)
2. Or set `GtagId = null`

The system will automatically skip GA loading if no tracking ID is provided.

## Debug Functions

With the updated code, you can use these functions in browser console:

```javascript
// Check current status
analyticsConsent.getDebugInfo();

// Test analytics manually
analyticsConsent.testAnalytics();

// Remove consent and reload
analyticsConsent.removeConsentAndReload();
```

## Common Issues

1. **Ad Blockers**: uBlock Origin, AdBlock, etc. will block GA requests
2. **CSP Headers**: Content Security Policy might block external scripts
3. **Invalid ID**: Make sure your tracking ID format is `G-XXXXXXXXXX`
4. **Network Issues**: Some networks/firewalls block Google domains

## Verifying It Works

1. **Console Logs**: Look for successful `[GA Debug]` messages
2. **Network Tab**: Check for requests to `google-analytics.com/g/collect`
3. **GA Real-time Reports**: Check your GA dashboard for real-time visitors
4. **GA4 DebugView**: Enable debug mode in GA4 to see events in real-time