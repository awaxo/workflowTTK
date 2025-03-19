import { Trigger } from '@form-validation/plugin-trigger';

try {
  FormValidation.plugins.Trigger = Trigger;
} catch (e) {}

export { Trigger };
