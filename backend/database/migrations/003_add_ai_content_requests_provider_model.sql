-- ==========================================================
-- Connect MyUni — Migration 003
-- Add AI provider / model metadata to ai_content_requests.
--
-- The request log previously could not record which provider
-- (e.g. 'openai') or model (e.g. 'gpt-4o-mini') produced a
-- generation. These two nullable columns add that metadata.
-- Existing rows are untouched and remain valid; the columns are
-- optional so historical records store NULL.
-- ==========================================================
ALTER TABLE IF EXISTS ai_content_requests ADD COLUMN IF NOT EXISTS provider VARCHAR(50);
ALTER TABLE IF EXISTS ai_content_requests ADD COLUMN IF NOT EXISTS model VARCHAR(100);