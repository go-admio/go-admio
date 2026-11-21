<?php
// ---------------------------------------------------------------
// Progress view for a running campaign.
// Included from sendmail.php after the batch has been processed.
// Must not emit anything before <!DOCTYPE> (quirks-mode safe).
// ---------------------------------------------------------------

// Defensive defaults: on a completed campaign, or a manual refresh
// of a finished one, the caller's foreach may not have run at all.
$TotalRecipients  = $TotalRecipients  ?? 0;
$RemainRecipients = (int) ($RemainRecipients ?? 0);
$Subject          = $Subject          ?? '';
$CampaignID       = $CampaignID       ?? '';

// Clamp so a stale TotalRecipients or a string COUNT(*) can never
// produce a negative width or a division-by-zero.
$N       = max(0, min($TotalRecipients - $RemainRecipients, $TotalRecipients));
$Percent = $TotalRecipients > 0 ? (int) round($N / $TotalRecipients * 100) : 0;
$Done    = $RemainRecipients <= 0;

// Escape anything that reaches the markup (subject is user-supplied).
$SafeSubject = htmlspecialchars((string) $Subject, ENT_QUOTES, 'UTF-8');
$SafeCampID  = htmlspecialchars((string) $CampaignID, ENT_QUOTES, 'UTF-8');

// 0 = reload immediately (max throughput). Set to 1 if a human is
// watching and you want the bar to be visibly animated per batch.
$RefreshDelay = 0;
$RefreshURL   = 'sendmail.php?CampaignID=' . urlencode($CampaignID) . '&T=' . time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if (!$Done): ?>
<meta http-equiv="refresh" content="<?= (int) $RefreshDelay ?>;URL=<?= htmlspecialchars($RefreshURL, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<title><?= $Done ? 'Campaign complete' : 'Sending campaign' ?> &mdash; <?= $SafeSubject ?></title>
<style>
*,*::before,*::after{box-sizing:border-box}

body{
  margin:0;min-height:100vh;
  display:flex;align-items:center;justify-content:center;
  padding:24px;
  background:
    radial-gradient(900px 460px at 50% -12%, rgba(255,106,0,.14), transparent 68%),
    #0a0a0a;
  color:#fff;
  font-family:'Inter',ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
  -webkit-font-smoothing:antialiased;
}

/* ---------- card ---------- */
.sm-card{
  width:100%;max-width:600px;
  padding:30px 28px 26px;
  background:#0d0d0d;
  border:1px solid #1e1e1e;border-radius:16px;
  box-shadow:0 24px 60px rgba(0,0,0,.55);
  animation:sm-in .35s ease both;
}
@keyframes sm-in{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}

/* ---------- badge ---------- */
.sm-badge{
  display:inline-flex;align-items:center;gap:8px;
  padding:5px 13px;border-radius:999px;
  background:rgba(255,106,0,.1);
  border:1px solid rgba(255,106,0,.32);
  color:#ffa366;font-size:12.5px;font-weight:600;letter-spacing:.02em;
  margin:0 0 18px;
}
.sm-dot{
  width:6px;height:6px;border-radius:50%;background:#FF6A00;
  box-shadow:0 0 8px #FF6A00;
  animation:sm-pulse 1.4s ease-in-out infinite;
}
.sm-badge-done .sm-dot{animation:none;background:#3ecf8e;box-shadow:0 0 8px #3ecf8e}
@keyframes sm-pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.35;transform:scale(.8)}}

/* ---------- subject ---------- */
.sm-subject{
  margin:0 0 10px;
  font-size:clamp(1.15rem,1rem + 1vw,1.5rem);
  font-weight:700;line-height:1.35;letter-spacing:-.01em;color:#fff;
  overflow-wrap:anywhere;
}
.sm-meta{
  margin:0 0 24px;color:#8e8e8e;font-size:.85rem;
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.sm-meta b{color:#d4d4d4;font-weight:600}

/* ---------- progress ---------- */
.sm-barhead{
  display:flex;align-items:baseline;justify-content:space-between;gap:12px;
  margin:0 0 9px;font-size:.85rem;
}
.sm-count{color:#8e8e8e}
.sm-count b{color:#fff;font-weight:700;font-variant-numeric:tabular-nums}
.sm-pct{color:#FF6A00;font-weight:700;font-size:.95rem;font-variant-numeric:tabular-nums}

.sm-track{
  position:relative;height:12px;border-radius:999px;
  background:#050505;border:1px solid #1e1e1e;
  overflow:hidden;
}
.sm-fill{
  position:relative;height:100%;border-radius:999px;
  background:linear-gradient(90deg,#ffb27a 0%,#FF6A00 55%,#ff4d00 100%);
  box-shadow:0 0 14px rgba(255,106,0,.45);
  transition:width .5s cubic-bezier(.4,0,.2,1);
  overflow:hidden;
}
.sm-fill::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(115deg,transparent 30%,rgba(255,255,255,.3) 50%,transparent 70%);
  background-size:48px 100%;background-repeat:repeat-x;
  animation:sm-stripes 1.1s linear infinite;
}
@keyframes sm-stripes{from{background-position:0 0}to{background-position:48px 0}}
.sm-is-done .sm-fill::after{display:none}

.sm-remain{
  display:flex;justify-content:space-between;
  margin:9px 0 0;font-size:.78rem;color:#8a8a8a;
  font-variant-numeric:tabular-nums;
}

/* ---------- footer note ---------- */
.sm-foot{
  display:flex;align-items:center;gap:9px;
  margin:22px 0 0;padding:13px 15px;
  background:rgba(255,106,0,.06);
  border-left:3px solid #FF6A00;border-radius:0 9px 9px 0;
  color:#cfcfcf;font-size:.83rem;line-height:1.5;
}
.sm-pulse{
  flex:none;width:7px;height:7px;border-radius:50%;
  background:#FF6A00;box-shadow:0 0 8px #FF6A00;
  animation:sm-pulse 1.2s ease-in-out infinite;
}
.sm-foot-done{border-left-color:#3ecf8e;background:rgba(62,207,142,.06);color:#c9e9db}
.sm-foot-done .sm-pulse{animation:none;background:#3ecf8e;box-shadow:0 0 8px #3ecf8e}

@media (max-width:480px){
  .sm-card{padding:24px 18px 20px;border-radius:14px}
  .sm-remain{font-size:.74rem}
}
@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation:none!important;transition:none!important}
}
</style>
</head>
<body>
<main class="sm-card<?= $Done ? ' sm-is-done' : '' ?>"
      data-status="<?= $Done ? 'complete' : 'sending' ?>"
      data-sent="<?= $N ?>"
      data-total="<?= $TotalRecipients ?>">

  <span class="sm-badge<?= $Done ? ' sm-badge-done' : '' ?>">
    <span class="sm-dot"></span>
    <?= $Done ? 'Campaign complete' : 'Sending campaign' ?>
  </span>

  <h1 class="sm-subject"><?= $SafeSubject ?></h1>

  <p class="sm-meta">
    <span>Campaign <b>#<?= $SafeCampID ?></b></span>
    <span>&middot;</span>
    <span><b><?= $TotalRecipients ?></b> recipients</span>
  </p>

  <div class="sm-barhead">
    <span class="sm-count"><b><?= $N ?></b> of <?= $TotalRecipients ?> sent</span>
    <span class="sm-pct"><?= $Percent ?>%</span>
  </div>

  <div class="sm-track" role="progressbar"
       aria-valuenow="<?= $Percent ?>" aria-valuemin="0" aria-valuemax="100"
       aria-label="Campaign progress">
    <div class="sm-fill" style="width:<?= $Percent ?>%"></div>
  </div>

  <div class="sm-remain">
    <span><?= $RemainRecipients ?> remaining</span>
    <span><?= $TotalRecipients ?> total</span>
  </div>

  <p class="sm-foot<?= $Done ? ' sm-foot-done' : '' ?>">
    <span class="sm-pulse"></span>
    <?= $Done
        ? 'All recipients processed. This campaign is finished.'
        : 'Continuing automatically &mdash; this page reloads after each batch.' ?>
  </p>

</main>
</body>
</html>